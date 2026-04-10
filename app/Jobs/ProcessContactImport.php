<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Sequence;
use App\Jobs\SendSequenceEmail;
use App\Models\ContactGroupItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;


class ProcessContactImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // allow up to 10 minutes for large imports
    public $tries = 3;

    public $filePath;
    public $userId;
    public $groupId;

    public function __construct($filePath, $userId, $groupId = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

    public function handle(): void
    {
        if (!file_exists($this->filePath)) {
            \Log::error('Import file does not exist', ['filePath' => $this->filePath]);
            return;
        }

        // Ensure group access still valid in case group was deleted
        if ($this->groupId && !ContactGroup::where('id', $this->groupId)->where('user_id', $this->userId)->exists()) {
            \Log::info('Group not found or not owned, setting groupId to null');
            $this->groupId = null;
        }

        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            \Log::error('Cannot open file', ['filePath' => $this->filePath]);
            return;
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            \Log::error('Cannot read headers');
            fclose($handle);
            unlink($this->filePath);
            return;
        }

        // Clean BOM and sanitize headers
        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
        $headers = array_map(fn($h) => Str::snake(strtolower(trim($h))), $headers);
        
        $emailIndex = array_search('email', $headers);
        $nameIndex = array_search('name', $headers);

        if ($emailIndex === false) {
            \Log::error('Email header not found', ['headers' => $headers]);
            fclose($handle);
            unlink($this->filePath);
            return;
        }

        \Log::info('Headers parsed', ['emailIndex' => $emailIndex, 'nameIndex' => $nameIndex]);

        $batch = [];
        $processed = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $email = isset($row[$emailIndex]) ? trim($row[$emailIndex]) : null;
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $meta = [];
            foreach ($headers as $index => $header) {
                if ($index !== $emailIndex && $index !== $nameIndex && isset($row[$index])) {
                    $meta[$header] = trim($row[$index]);
                }
            }

            $batch[] = [
                'user_id' => $this->userId,
                'email' => $email,
                'name' => ($nameIndex !== false) ? trim($row[$nameIndex] ?? '') : null,
                'meta' => json_encode($meta),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                $this->saveBatch($batch);
                $processed += count($batch);
                \Log::info('Batch processed', ['batchSize' => count($batch), 'totalProcessed' => $processed]);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            $this->saveBatch($batch);
            $processed += count($batch);
            \Log::info('Final batch processed', ['batchSize' => count($batch), 'totalProcessed' => $processed]);
        }

        fclose($handle);
        unlink($this->filePath); // Delete the temporary file when done

        \Log::info('Import completed', ['totalProcessed' => $processed]);
    }

    private function saveBatch($batch)
{
    Contact::upsert($batch, ['user_id', 'email'], ['name', 'meta', 'updated_at']);

    if (!$this->groupId) {
        return;
    }

    $emails = array_column($batch, 'email');
    $contactIds = Contact::where('user_id', $this->userId)
        ->whereIn('email', $emails)
        ->pluck('id');

    if ($contactIds->isEmpty()) {
        return;
    }

    $existing = ContactGroupItem::where('contact_group_id', $this->groupId)
        ->whereIn('contact_id', $contactIds)
        ->pluck('contact_id')
        ->toArray();

    $groupItems = [];
    foreach ($contactIds as $id) {
        if (!in_array($id, $existing, true)) {
            $groupItems[] = [
                'contact_id' => $id,
                'contact_group_id' => $this->groupId,
            ];
        }
    }

    if (!empty($groupItems)) {
        ContactGroupItem::insert($groupItems);

        // Trigger automation for the whole batch
        $sequences = Sequence::where('group_id', $this->groupId)->with('steps')->get();
        $contacts = Contact::whereIn('id', $contactIds)->get();

        foreach ($sequences as $sequence) {
            foreach ($sequence->steps as $step) {
                foreach ($contacts as $contact) {
                    dispatch(new SendSequenceEmail($contact, $step))
                        ->delay(now()->addDays($step->delay_days));
                }
            }
        }
    }
}
}
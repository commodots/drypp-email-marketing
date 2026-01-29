<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\ContactGroupItem;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
       $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'name' => 'Test User'
            ]);
        }

        $group = ContactGroup::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'VIP Leads']
        );

        for ($i = 1; $i <= 20; $i++) {
            $contact = Contact::updateOrCreate(
                ['email' => "lead{$i}@test.com"],
                ['user_id' => $user->id]
            );

            // Link to the group
            ContactGroupItem::firstOrCreate([
                'contact_id' => $contact->id,
                'contact_group_id' => $group->id
            ]);
        }
    }
}
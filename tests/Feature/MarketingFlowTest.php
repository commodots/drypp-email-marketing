<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Sequence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MarketingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
        $this->actingAs($this->user);
    }

    /** @test */
    public function campaign_structure_and_tracking_work()
    {
        Mail::fake();

        // 1. Create a campaign
        $campaign = Campaign::create([
            'user_id' => $this->user->id,
            'name' => 'Test Campaign',
            'sender_email' => 'sender@test.com',
            'status' => 'draft',
            'type' => 'bulk'
        ]);

        $campaign->emailContent()->create([
            'subject' => 'Hello {{ name }}',
            'body' => '<a href="https://example.com">Click Me</a>',
            'format' => 'html'
        ]);

        $this->assertDatabaseHas('campaigns', ['name' => 'Test Campaign']);

        // 2. Simulate sending record
        $message = CampaignMessage::create([
            'campaign_id' => $campaign->id,
            'email' => 'recipient@test.com',
            'status' => 'pending'
        ]);

        // 3. Test Open Tracking
        $this->get(route('track.open', $message->id));
        $this->assertNotNull($message->fresh()->opened_at);
        $this->assertEquals(1, $campaign->fresh()->opens);

        // 4. Test Click Tracking
        $this->get(route('track.click', $message->id) . '?redirect=https://google.com');
        $this->assertNotNull($message->fresh()->clicked_at);
        $this->assertEquals(1, $campaign->fresh()->clicks);
    }

    /** @test */
    public function list_health_bounce_cleaning_works()
    {
        $contact = Contact::create([
            'user_id' => $this->user->id,
            'email' => 'bounced@test.com',
            'status' => 'active'
        ]);

        $campaign = Campaign::create(['user_id' => $this->user->id, 'name' => 'Test']);
        CampaignMessage::create([
            'campaign_id' => $campaign->id,
            'email' => $contact->email,
            'status' => 'failed'
        ]);

        // Run the cleaning command
        \Illuminate\Support\Facades\Artisan::call('emails:clean-bounced');

        $this->assertEquals('bounced', $contact->fresh()->status);
    }

    /** @test */
    public function automation_trigger_works()
    {
        Queue::fake();

        $group = ContactGroup::create(['user_id' => $this->user->id, 'name' => 'Subscribers']);
        $sequence = Sequence::create(['user_id' => $this->user->id, 'name' => 'Welcome', 'group_id' => $group->id]);
        $sequence->steps()->create([
            'delay_days' => 1,
            'subject' => 'Welcome!',
            'body' => 'Hi there'
        ]);

        // Adding contact to group should trigger automation
        $this->post(route('contacts.store'), [
            'email' => 'new@test.com',
            'group_id' => $group->id,
            'name' => 'Test User'
        ]);

        Queue::assertPushed(\App\Jobs\SendSequenceEmail::class);
    }
}

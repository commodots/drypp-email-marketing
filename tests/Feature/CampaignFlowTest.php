<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_campaign_redirects_to_show_page_with_success_message()
    {
        $user = User::factory()->create();
        $group = ContactGroup::create(['user_id' => $user->id, 'name' => 'Test Group']);

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => 'HTML Campaign',
            'subject' => 'Hello',
            'type' => 'cold',
            'format' => 'html',
            'body' => '<h1>Preview Me</h1>',
            'group_id' => $group->id
        ]);

        $campaign = Campaign::first();

    }

    public function test_text_campaign_redirects_straight_to_index()
    {
        $user = User::factory()->create();
        $group = ContactGroup::create(['user_id' => $user->id, 'name' => 'Test Group']);

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => 'Text Campaign',
            'subject' => 'Hello',
            'type' => 'cold',
            'format' => 'text',
            'body' => 'Just plain text',
            'group_id' => $group->id
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $response->assertSessionHas('success', 'Text campaign saved successfully.');
    }

   public function test_user_can_send_to_individual_contacts()
    {
        $user = User::factory()->create();
        
        $packageId = \Illuminate\Support\Facades\DB::table('packages')->insertGetId([
            'name' => 'Test Package',
            'type' => 'cold_email',
            'price_ngn' => 0,
            'email_limit' => 0,
            'lead_limit' => 0,  
        ]);

        // 2. Create the Subscription
        \App\Models\Subscription::create([
            'user_id' => $user->id,
            'package_id' => $packageId,
            'emails_used' => 0,
            'status' => 'active',
            'expires_at' => now()->addMonth(),
        ]);

        $campaign = Campaign::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        
        $c1 = Contact::factory()->create(['user_id' => $user->id, 'email' => 'a@test.com']);
        $c2 = Contact::factory()->create(['user_id' => $user->id, 'email' => 'b@test.com']);

        $response = $this->actingAs($user)->post(route('campaigns.send', $campaign), [
            'contact_ids' => [$c1->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_messages', ['email' => 'a@test.com']);
    }
}
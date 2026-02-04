<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SmtpServer;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSmtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_smtp_server()
    {
        $admin = User::factory()->create(['email' => 'admin@test.com', 'role' => 'admin']); 

        $response = $this->actingAs($admin)->post(route('admin.smtps.store'), [
            'name' => 'Mailgun Main',
            'host' => 'smtp.mailgun.org',
            'daily_limit' => 1000,
        ]);

        $this->assertDatabaseHas('smtp_servers', ['name' => 'Mailgun Main']);
    }

    public function test_admin_can_hot_swap_servers_on_active_campaign()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        // 1. Create two servers
        $serverA = SmtpServer::create(['name' => 'Server A', 'host' => 'a', 'daily_limit' => 100]);
        $serverB = SmtpServer::create(['name' => 'Server B', 'host' => 'b', 'daily_limit' => 100]);

        // 2. Campaign is running on Server A
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id, 
            'status' => 'sending',
            'smtp_id' => $serverA->id
        ]);

        // 3. Admin switches to Server B (Hot Swap)
        $response = $this->actingAs($admin)->post(route('admin.campaigns.assign', $campaign->id), [
            'smtp_id' => $serverB->id
        ]);

        // 4. Assert Database Updated
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'smtp_id' => $serverB->id // Should now be B
        ]);
    }
}
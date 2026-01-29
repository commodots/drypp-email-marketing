<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class UserSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        $package = Package::first() ?? Package::create([
            'name' => 'Pro Plan',
            'type' => 'email_marketing',
            'email_limit' => 10000,
            'price_ngn' => 25000,
        ]);

        if ($user) {
            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'package_id' => $package->id,
                    'status' => 'active',
                    'emails_used' => 100, 
                    'expires_at' => now()->addDays(30),
                    'leads_used' => 50,
                ]
            );
        }
    }
}
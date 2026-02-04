<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\ContactGroupItem;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $user = User::where('email', 'test@example.com')->first();

        // 1. Create Multiple Groups
        $groupNames = ['VIP Leads', 'Newsletter Subscriptions', 'Past Customers', 'Inbound Inquiries'];
        $groups = [];

        foreach ($groupNames as $name) {
            $groups[] = ContactGroup::firstOrCreate([
                'user_id' => $user->id,
                'name' => $name
            ]);
        }

        // 2. Create 50 Real Contacts
        for ($i = 1; $i <= 50; $i++) {
            $email = $faker->unique()->safeEmail;

            $contact = Contact::updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'created_at' => $faker->dateTimeBetween('-30 days', 'now'),
                ]
            );

            // Randomly link to 1 or 2 groups
            $randomGroup = $groups[array_rand($groups)];
            ContactGroupItem::firstOrCreate([
                'contact_id' => $contact->id,
                'contact_group_id' => $randomGroup->id
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;



class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::updateOrCreate(['name' => 'Starter'], [
            'type' => 'cold_email',
            'email_limit' => 50000,
            'lead_limit' => 1000,
            'price_ngn' => 30000,
        ]);

    Package::updateOrCreate(['name' => 'Pro'], [
            'type' => 'cold_email',
            'email_limit' => 250000,
            'lead_limit' => 10000,
            'price_ngn' => 75000,
        ]);

        Package::updateOrCreate([
            'name' => 'Business',
            'type' => 'cold_email',
            'email_limit' => 1000000,
            'lead_limit' => 500000,
            'price_ngn' => 250000,
        ]);

        Package::updateOrCreate([
            'name' => 'Enterprise',
            'type' => 'email_marketing',
            'email_limit' => 5000000,
            'lead_limit' => null,
            'price_ngn' => 500000,
        ]);
    }
}

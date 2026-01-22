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
        Package::updateOrCreate([
            'name' => 'Cold Call Basic',
            'type' => 'cold_call',
            'email_quota' => 10000,
            'lead_quota' => 50000,
            'price' => 100,
        ]);

    Package::updateOrCreate([
            'name' => 'Cold Call Pro',
            'type' => 'cold_call',
            'email_quota' => 50000,
            'lead_quota' => 100000,
            'price' => 200,
        ]);

        Package::updateOrCreate([
            'name' => 'Cold Call Premium',
            'type' => 'cold_call',
            'email_quota' => 300000,
            'lead_quota' => 500000,
            'price' => 500,
        ]);

        Package::updateOrCreate([
            'name' => 'Email Marketing Monthly',
            'type' => 'email_marketing',
            'email_quota' => 10000,
            'lead_quota' => null,
            'price' => 50,
        ]);
    }
}

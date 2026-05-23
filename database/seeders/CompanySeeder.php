<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        // Create 100 users
        for ($i = 0; $i < 50; $i++) {
            // Generate random user details
           Company::create([
                'name' => $faker->company(),
                'slug' => Str::slug($faker->name),
                'status' => ACTIVE_STATUS, // active or any other status
            ]);
        }
    }
}

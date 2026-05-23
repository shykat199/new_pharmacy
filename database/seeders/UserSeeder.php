<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        // Create 100 users
        for ($i = 0; $i < 200; $i++) {
            // Generate random user details
            $user = User::create([
                'name' => $faker->name,
                'slug' => Str::slug($faker->name),
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'role' => USER_ROLE, // or any role you want
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'), // default password
                'address' => $faker->address(),
                'status' => ACTIVE_STATUS, // active or any other status
                'balance' => 0, // active or any other status

            ]);
        }
    }
}

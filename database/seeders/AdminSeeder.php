<?php

namespace Database\Seeders;

use App\Models\AdminAccessPassword;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $data=[
            'name' => 'Admin',
            'slug' => Str::slug('Admin'),
            'email' => 'admin@gmail.com',
            'phone' => '01XXXXXXXX',
            'role' => ADMIN_ROLE, // or any role you want
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // default password
            'address' => $faker->address(),
            'status' => ACTIVE_STATUS, // active or any other status
            'balance' => 0, // active or any other status

        ];
       $user =  User::firstOrCreate(
            ['email' => $data['email']],
            $data
        );

       if ($user){
           AdminAccessPassword::updateOrCreate(
               ['user_id'=>$user->id],
               ['password'=>Hash::make('12345678'),]
           );
       }
    }
}

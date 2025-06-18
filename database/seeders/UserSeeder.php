<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * php artisan db:seed --class=UserSeeder
     */
    public function run()
    {
        $users = [
            
            // [
            //     'name' => 'Oliver De Vera',
            //     'birthdate' => '1992-08-21',
            //     'birthplace' => 'Artacho Sison Pangasinan',
            //     'phone_number' => '9053192544',
            //     'gender' => 'Male',
            //     'status' => 'Single',
            //     'address' => 'Artacho Sison Pangasinans',
            //     'department' => 'Administrators',
            //     'position' => 'Executive Secretary, Human Resource',
            //     'designation' => 'Northern Luzon Mission Office Workers',
            //     'work_status' => 'Regular',
            //     'category' => 'Ministerial Credential',
            //     'img' => '1750052088.jpg',
            //     'acc_code' => '1003',
            //     'reg_approval' => '2025-05-05',
            //     'email' => 'oliverdevera@gmail.com',
            //     'email_verified_at' => Carbon::now(),
            //     'password' => Hash::make('password123'),
            //     'remember_token' => Str::random(10),
            //     'created_at' => Carbon::now(),
            //     'updated_at' => Carbon::now(),
            // ],
            [
                'name' => 'Jenelyn Pagalillauan',
                'birthdate' => '1985-05-10',
                'birthplace' => 'Artacho Sison Pangasinan',
                'phone_number' => '09155135666',
                'gender' => 'Female',
                'status' => 'Married',
                'address' => 'Artacho Sison Pangasinan',
                'department' => 'Secretariat',
                'position' => 'Executive Secretary',
                'designation' => 'Northern Luzon Mission Office Workers',
                'work_status' => 'Contractual',
                'category' => 'Ministerial Credential',
                'img' => null,
                'acc_code' => '1004',
                'reg_approval' => '2025-05-05',
                'email' => 'jen@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Resh Ramos',
                'birthdate' => '1985-05-10',
                'birthplace' => 'Artacho Sison Pangasinan',
                'phone_number' => '9155135666',
                'gender' => 'Female',
                'status' => 'Single',
                'address' => 'Artacho Sison Pangasinan',
                'department' => 'Accounting Personnel',
                'position' => 'Disbursing Accountant',
                'designation' => 'Northern Luzon Mission Office Workers',
                'work_status' => 'Probationary',
                'category' => 'None',
                'img' => null,
                'acc_code' => '1002',
                'reg_approval' => '2025-05-05',
                'email' => 'resh@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ismael Cabason',
                'birthdate' => '1985-05-10',
                'birthplace' => 'Artacho Sison Pangasinan',
                'phone_number' => '9155135666',
                'gender' => 'Male',
                'status' => 'Married',
                'address' => 'Artacho Sison Pangasinan',
                'department' => 'Administrators',
                'position' => 'President',
                'designation' => 'Northern Luzon Mission Office Workers',
                'work_status' => 'Ordained',
                'category' => 'Ministerial Licensed',
                'img' => null,
                'acc_code' => null,
                'reg_approval' => '2025-05-05',
                'email' => 'icabason@adventist.ph',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Miller Payoyo',
                'birthdate' => '1992-08-21',
                'birthplace' => 'Artacho Sison Pangasinan',
                'phone_number' => '9171296605',
                'gender' => 'Male',
                'status' => 'Married',
                'address' => 'Artacho Sison Pangasinan',
                'department' => 'Administrator',
                'position' => 'Treasurer',
                'designation' => 'Northern Luzon Mission Office Worker',
                'work_status' => 'Part-time',
                'category' => 'Staff',
                'img' => null,
                'acc_code' => null,
                'reg_approval' => '2025-05-05',
                'email' => 'mpayoyo@yahoo.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // Insert the users into the database
        User::insert($users);
    }
}
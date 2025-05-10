<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LeavetypeSeeder extends Seeder
{
    /**
     * php artisan db:seed --class=LeavetypeSeeder
     */
    public function run()
    {
        DB::table('leavetypes')->insert([
            
            [
                'type' => 'Day/s off | Annual Vacation',
                'days_allowed' => 0,
                'description' => 'Vacation Casual Leave',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Sick',
                'days_allowed' => 10,
                'description' => 'Sickness',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Compassionate',
                'days_allowed' => 10,
                'description' => 'Compassionate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Maternity',
                'days_allowed' => 30,
                'description' => 'Pregnant',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Paternity',
                'days_allowed' => 15,
                'description' => 'Fatherhood',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

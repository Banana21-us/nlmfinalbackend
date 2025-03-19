<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DesignationSeeder extends Seeder
{
    /**
     * php artisan db:seed --class=DesignationSeeder
     */
    public function run(): void
    {
        $designations = [
            ['id' => 1, 'name' => 'Northern Luzon Mission Office Workers'],
            ['id' => 2, 'name' => 'Northern Luzon Mission Field Workers'],
            ['id' => 3, 'name' => 'Western Tarlac Adv. Multigrade School'],
            ['id' => 4, 'name' => 'Rodrigo Agustin Fernando Memorial Adv. School'],
            ['id' => 5, 'name' => 'San Felipe Adventist Mult. School Inc.'],
            ['id' => 7, 'name' => 'Adventist School of Urbiztondo Inc.'],
            ['id' => 8, 'name' => 'Camiling Adventist School Inc.'],
            ['id' => 9, 'name' => 'Carino Adventist School Inc.'],
            ['id' => 10, 'name' => 'Carosucan Adventist Multigrade School Inc.'],
            ['id' => 11, 'name' => 'Central Pangasinan Adventist School Inc.'],
            ['id' => 12, 'name' => 'Ilocos Norte Adventist School Inc.'],
            ['id' => 13, 'name' => 'Laoac Adventist Mission School Inc.'],
            ['id' => 14, 'name' => 'Malasiqui Adventist School Inc.'],
            ['id' => 15, 'name' => 'Manaoag Adventist School Inc.'],
            ['id' => 16, 'name' => 'Abra District Adventist Multigrade School Inc.'],
            ['id' => 17, 'name' => 'Adventist School Galimuyod Campus Inc.'],
            ['id' => 18, 'name' => 'Adventist School La Ciudad Fernandina Inc.'],
            ['id' => 19, 'name' => 'Adventist School City of San Fernando La Union Inc.'],
            ['id' => 20, 'name' => 'Adventist School Tagudin Campus Inc.'],
            ['id' => 21, 'name' => 'Western Tarlac Adv. Multigrade School'],
            ['id' => 22, 'name' => 'Rodrigo Agustin Fernando Memorial Adv. School'],
            ['id' => 23, 'name' => 'Rosales Adventist Mult School Inc.'],
            ['id' => 24, 'name' => 'San Felipe Adventist Mult. School Inc.'],
        ];

        foreach ($designations as $designation) {
            DB::table('designations')->updateOrInsert(
                ['id' => $designation['id']],
                [
                    'name' => $designation['name'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * php artisan db:seed --class=CategorySeeder
     */
    public function run(): void
    {
        $categories = [
            'Ministerial Credential',
            'Ministerial Licensed',
            'Missionary Credential',
            'Regular Certificate',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

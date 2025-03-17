<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    /**
     * php artisan db:seed --class=DepartmentSeeder
     */
    public function run(): void
    {
        $departments = [
            ['id' => 1, 'name' => 'Directors'],
            ['id' => 2, 'name' => 'Administrators'],
            ['id' => 3, 'name' => 'Secretariat'],
            ['id' => 4, 'name' => 'Accounting Personnel'],
            ['id' => 5, 'name' => 'Driver/Maintenance'],
            ['id' => 6, 'name' => 'Media/IT'],
            ['id' => 7, 'name' => 'Pastors'],
            ['id' => 8, 'name' => 'Leaders'],
            ['id' => 9, 'name' => 'Teachers'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['id' => $department['id']],
                [
                    'name' => $department['name'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );
        }
    }
}

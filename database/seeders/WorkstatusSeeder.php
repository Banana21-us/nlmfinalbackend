<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkstatusSeeder extends Seeder
{
   
    //   php artisan db:seed --class=WorkstatusSeeder
    //   php artisan db:seed --class=PositionSeeder
    //   php artisan db:seed --class=DesignationSeeder
    //   php artisan db:seed --class=DepartmentSeeder
    //   php artisan db:seed --class=CategorySeeder

     
    public function run(): void
    {
        $workStatuses = [
            'Ordained', 'Regular', 'Intern', 'Volunteer', 'Contractual',
            'Probationary', 'Pre Intern', 'Missionary Volunteer', 'None'
        ];

        foreach ($workStatuses as $status) {
            DB::table('workstatuses')->insert([
                'name' => $status,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\Carbon;
class PositionSeeder extends Seeder
{
    /**
     * using php artisan db:seed --class=PositionSeeder
     */
    public function run(): void
    {
        $positions = [
            'Head Teacher', 'Teacher', 'Women Ministry', 'Family Ministry', 'Music Ministry',
            'Ministerial', 'Nurture Discipleship Retention Integrated Evangelion Lifestyle',
            'Education', 'Adventist Children Ministry', 'Ministerial Spouse Association',
            'Adventist Possibility Ministry', 'Communication Ministry', 'Health Ministry',
            'Publishing Ministry', 'Spirit of Prophecy', 'Youth Ministries', 'President',
            'Executive Secretary', 'Treasurer', 'Media', 'AWR Producer', 'Driver',
            'Maintenance', 'IT', 'Administrative Assistant', 'Auditor', 'Receiving Accountant',
            'Chief Accountant', 'Church Auditor', 'Accounting Staff', 'Disbursing Accountant',
            'Sabbath School Personal Ministries', 'Adventist Community Service',
            'Stewardship Ministry', 'Adventist Laymen Services Institute', 'APML'
        ];

        foreach ($positions as $position) {
            DB::table('positions')->insert([
                'name' => $position,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

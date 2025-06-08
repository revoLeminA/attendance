<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $year = 2024;
        $month = 12;

        // 8:00出社, 18:00退社, 2か月分のデータ
        for ($i=0; $i<2; $i++) {
            $calender = Carbon::create($year, $month, 01);
            for ($j=0; $j<$calender->daysInMonth; $j++) {
                DB::table('attendances')->insert([
                    'user_id' => User::first()->id,
                    'status' => '退勤済',
                    'date' => $calender->copy()->addDays($j)->format('Y/m/d'),
                    'clock_in' => $calender->copy()->addDays($j)->addHour(9)->format('H:i'),
                    'clock_out' => $calender->copy()->addDays($j)->addHour(18)->format('H:i'),
                    'created_at' => $calender->copy()->addDays($j)->addHour(9),
                    'updated_at' => $calender->copy()->addDays($j)->addHour(18),
                ]);
            }
            if (++$month >= 13) {
                $year++;
                $month = 1;
            }else {
                $month++;
            }
        }

        // 例外テスト用データ
        DB::table('attendances')->insert([
            'user_id' => User::first()->id,
            'status' => '退勤済',
            'date' => Carbon::create(2025, 02, 01)->format('Y/m/d'),
            'clock_in' => Carbon::create(2025, 02, 01)->addHour(9)->format('H:i'),
            'clock_out' => Carbon::create(2025, 02, 01)->addHour(18)->addMinute(30)->format('H:i'),
            'created_at' => Carbon::create(2025, 02, 01)->addHour(9),
            'updated_at' => Carbon::create(2025, 02, 01)->addHour(18)->addMinute(30),
        ]);
        DB::table('attendances')->insert([
            'user_id' => User::first()->id,
            'status' => '退勤済',
            'date' => Carbon::create(2025, 02, 02)->format('Y/m/d'),
            'clock_in' => Carbon::create(2025, 02, 02)->addHour(9)->format('H:i'),
            // 'clock_out' => Carbon::create(2025, 02, 01)->addHour(18)->format('H:i'),
            'created_at' => Carbon::create(2025, 02, 02)->addHour(9),
            'updated_at' => Carbon::create(2025, 02, 02)->addHour(9),
        ]);
}
}

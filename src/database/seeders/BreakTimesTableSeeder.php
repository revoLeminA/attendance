<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // 休憩1回
            DB::table('break_times')->insert([
                'attendance_id' => $attendance->id,
                'break_start' => $attendance->date->copy()->addHour(12)->format('H:i'),
                'break_end' => $attendance->date->copy()->addHour(13)->format('H:i'),
                'created_at' => $attendance->date->copy()->addHour(12),
                'updated_at' => $attendance->date->copy()->addHour(13),
            ]);
            // 休憩2回
            if ($attendance->date->isSameDay(Carbon::parse('2025-02-01'))) {
                DB::table('break_times')->insert([
                    'attendance_id' => $attendance->id,
                    'break_start' => $attendance->date->copy()->addHour(15)->format('H:i'),
                    'break_end' => $attendance->date->copy()->addHour(15)->addMinute(30)->format('H:i'),
                    'created_at' => $attendance->date->copy()->addHour(15),
                    'updated_at' => $attendance->date->copy()->addHour(15)->addMinute(30),
                ]);
            }
        }
    }
}

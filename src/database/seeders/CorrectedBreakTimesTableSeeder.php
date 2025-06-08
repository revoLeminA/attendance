<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectedAttendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class CorrectedBreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $corrected_attendances = CorrectedAttendance::all();
        $break_times = BreakTime::all();


        foreach ($corrected_attendances as $corrected_attendance) {
            // 休憩1回
            DB::table('corrected_break_times')->insert([
                'break_time_id' => $break_times->where('attendance_id', $corrected_attendance->attendance_id)->first()->id,
                'corrected_attendance_id' => $corrected_attendance->id,
                'corrected_break_start' => $corrected_attendance->corrected_date->copy()->addHour(12)->format('H:i'),
                'corrected_break_end' => $corrected_attendance->corrected_date->copy()->addHour(13)->format('H:i'),
                'created_at' => $corrected_attendance->corrected_date->copy()->addHour(9)->addDay(),
                'updated_at' => $corrected_attendance->corrected_date->copy()->addHour(9)->addDay(),
            ]);
            // 休憩2回
            if ($corrected_attendance->corrected_date->isSameDay(Carbon::parse('2025-02-01'))) {
                DB::table('corrected_break_times')->insert([
                    'break_time_id' => $break_times->where('attendance_id', $corrected_attendance->attendance_id)->first()->id+1,
                    'corrected_attendance_id' => $corrected_attendance->id,
                    'corrected_break_start' => $corrected_attendance->corrected_date->copy()->addHour(15)->format('H:i'),
                    'corrected_break_end' => $corrected_attendance->corrected_date->copy()->addHour(16)->addMinute(30)->format('H:i'),
                    'created_at' => $corrected_attendance->corrected_date->copy()->addHour(9)->addDay(),
                    'updated_at' => $corrected_attendance->corrected_date->copy()->addHour(9)->addDay(),
                ]);
            }
        }
    }
}

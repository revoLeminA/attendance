<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class CorrectedAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $calender = Carbon::create(2025, 02, 01);

        DB::table('corrected_attendances')->insert([
            'user_id' => User::first()->id,
            'attendance_id' => Attendance::where('date', $calender)->first()->id,
            'status' => '承認待ち',
            'corrected_date' => $calender->copy()->format('Y/m/d'),
            'corrected_clock_in' => $calender->copy()->addHour(9)->format('H:i'),
            'corrected_clock_out' => $calender->copy()->addHour(20)->format('H:i'),
            'corrected_reason' => '残業のため',
            'created_at' => $calender->copy()->addHour(9)->addDay(),
            'updated_at' => $calender->copy()->addHour(9)->addDay(),
        ]);

        $calender = Carbon::create(2025, 02, 02);

        DB::table('corrected_attendances')->insert([
            'user_id' => User::first()->id,
            'attendance_id' => Attendance::where('date', $calender)->first()->id,
            'status' => '承認待ち',
            'corrected_date' => $calender->copy()->format('Y/m/d'),
            'corrected_clock_in' => $calender->copy()->addHour(9)->format('H:i'),
            'corrected_clock_out' => $calender->copy()->addHour(14)->format('H:i'),
            'corrected_reason' => '体調不良のため早退',
            'created_at' => $calender->copy()->addHour(9)->addDay(),
            'updated_at' => $calender->copy()->addHour(9)->addDay(),
        ]);
    }
}

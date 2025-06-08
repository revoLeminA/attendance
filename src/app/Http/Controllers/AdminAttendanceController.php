<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面
    public function index(Request $request)
    {
        // 勤怠を表示するユーザ
        $users = User::all();

        // 表示したい年（デフォルトは今日の年）
        $thisYear = $request->input('year') ?? Carbon::today()->format('Y');
        // 表示したい月（デフォルトは今日の月）
        $thisMonth = $request->input('month') ?? Carbon::today()->format('m');
        // 表示したい日（デフォルトは今日の日）
        $thisDay = $request->input('day') ?? Carbon::today()->format('d');
        // 表示する年月日
        $thisYMD = Carbon::Create($thisYear, $thisMonth, $thisDay);
        // 表示する年月日の勤怠
        $thisAttendances = Attendance::where('date', $thisYMD->format('Y-m-d'))->get();
        // 表示する年月日の休憩時間、勤務時間の合計
        $thisBreakTimes = [];
        $thisWorkTimes = [];
        foreach ($thisAttendances as $thisAttendance) {
            // 休憩時間の合計
            $rowBreakTimes = BreakTime::where('attendance_id', $thisAttendance->id)->get();
            $tmpBreakTime = 0;
            foreach ($rowBreakTimes as $rowBreakTime) {
                if (isset($rowBreakTime->break_end)) {
                    $tmpBreakTime += $rowBreakTime->break_end->diffInMinutes($rowBreakTime->break_start);
                }
            }
            array_push($thisBreakTimes, ['id' => $thisAttendance->id, 'break_time' => round($tmpBreakTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpBreakTime % 60, 2, '0', STR_PAD_LEFT)]);
            // 勤務時間の合計
            if (isset($thisAttendance->clock_out)) {
                $tmpWorkTime = $thisAttendance->clock_out->diffInMinutes($thisAttendance->clock_in) - $tmpBreakTime;
                array_push($thisWorkTimes, ['id' => $thisAttendance->id, 'work_time' => round($tmpWorkTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpWorkTime % 60, 2, '0', STR_PAD_LEFT)]);
            }
        }

        // 前日
        $previousYMD = $thisYMD->copy()->subDay();
        // 翌日
        $nextYMD = $thisYMD->copy()->addDay();

        return view('admin.attendance.index', compact('users', 'thisAttendances', 'thisBreakTimes', 'thisWorkTimes', 'thisYMD', 'previousYMD', 'nextYMD'));
    }
}

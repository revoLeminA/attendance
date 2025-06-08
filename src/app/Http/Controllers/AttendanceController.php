<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    // 勤怠登録画面
    public function create(Request $request)
    {
        // 勤怠登録画面に表示する日時情報
        $now = Carbon::now();
        $currentDate = $now->copy()->format('Y年m月d日');
        $currentTime = $now->copy()->format('H:i');

        $status = '勤務外';
        if (Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->exists()) {
            $status = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first()->status;
        }

        return view('user.attendance.create', compact('currentDate', 'currentTime', 'status'));
    }

    // 勤怠登録処理
    public function store(Request $request)
    {
        $now = new Carbon($request->pushed_at);

        if ($request->status === '勤務外') {
            // 出勤処理
            Attendance::create([
                'user_id' => Auth::id(),
                'date' => $now->copy()->format('Y/m/d'),
                'status' => '勤務中',
                'clock_in' => $now->copy()->format('H:i'),
            ]);
            return redirect()->route('user.attendance.create')->with('success', '出勤処理が完了しました。');
        } else if ($request->status === '勤務中') {
            // 休憩開始処理
            $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();
            $attendance->update([
                'status' => '休憩中',
            ]);
            $attendance->touch();
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $now->copy()->format('H:i'),
            ]);
            return redirect()->route('user.attendance.create')->with('success', '休憩開始処理が完了しました。');
        } else {
            return redirect()->route('user.attendance.create')->with('error', '処理が完了できませんでした。');
        }
    }

    // 勤怠更新処理
    public function update(Request $request)
    {
        $now = new Carbon($request->pushed_at);

        if ($request->status === '勤務中') {
            // 退勤処理
            Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first()->update([
                'status' => '退勤済',
                'clock_out' => $now->copy()->format('H:i'),
            ]);
            Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first()->touch();
            return redirect()->route('user.attendance.create')->with('success', '退勤処理が完了しました。');
        } else if ($request->status === '休憩中') {
            // 休憩終了処理
            $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();
            $attendance->update([
                'status' => '勤務中',
            ]);
            $attendance->touch();
            BreakTime::where('attendance_id', $attendance->id)->first()->update([
                'break_end' => $now->copy()->format('H:i'),
            ]);
            BreakTime::where('attendance_id', $attendance->id)->first()->touch();
            return redirect()->route('user.attendance.create')->with('success', '休憩終了処理が完了しました。');
        } else {
            return redirect()->route('user.attendance.create')->with('error', '処理が完了できませんでした。');
        }
    }

    // 勤怠一覧画面
    public function index(Request $request)
    {
        // 勤怠を表示するユーザ
        $user = Auth::user();

        // 表示したい年（デフォルトは今日の年）
        $thisYear = $request->input('year') ?? Carbon::today()->format('Y');
        // 表示したい月（デフォルトは今日の月）
        $thisMonth = $request->input('month') ?? Carbon::today()->format('m');
        // 表示する年月の初日
        $thisYM = Carbon::Create($thisYear, $thisMonth, 01);
        // 表示する月の日付
        $thisDays = [];
        for ($i = 0; $i < $thisYM->daysInMonth; $i++) {
            array_push($thisDays, $thisYM->copy()->addDays($i));
        }

        // 表示する年月の勤怠
        $thisAttendances = Attendance::where('user_id', $user->id)->whereBetween('date', [$thisYM->format('Y-m-d'), $thisYM->copy()->endOfMonth()->format('Y-m-d')])->get();
        // 表示する年月の休憩時間、勤務時間
        $thisBreakTimes = [];
        $thisWorkTimes = [];
        foreach ($thisAttendances as $thisAttendance) {
            // 各勤怠の休憩時間
            $rowBreakTimes = BreakTime::where('attendance_id', $thisAttendance->id)->get();
            $tmpBreakTime = 0;
            $isBreakTimeExists = false;
            foreach ($rowBreakTimes as $rowBreakTime) {
                if (isset($rowBreakTime->break_end)) { // 休憩終了時間がある場合
                    $isBreakTimeExists = true;
                    $tmpBreakTime += $rowBreakTime->break_end->diffInMinutes($rowBreakTime->break_start);
                }
            }
            if ($isBreakTimeExists) {
                array_push($thisBreakTimes, ['id' => $thisAttendance->id, 'break_time' => round($tmpBreakTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpBreakTime % 60, 2, '0', STR_PAD_LEFT)]);
            }
            // 各勤怠の勤務時間
            if (isset($thisAttendance->clock_out)) { // 退勤時間がある場合
                $tmpWorkTime = $thisAttendance->clock_out->diffInMinutes($thisAttendance->clock_in) - $tmpBreakTime;
                array_push($thisWorkTimes, ['id' => $thisAttendance->id, 'work_time' => round($tmpWorkTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpWorkTime % 60, 2, '0', STR_PAD_LEFT)]);
            }
        }

        // 前月
        $previousYM = $thisYM->copy()->subMonth();
        // 翌月
        $nextYM = $thisYM->copy()->addMonth();

        return view('user.attendance.index', compact('user', 'thisDays', 'thisAttendances', 'thisBreakTimes', 'thisWorkTimes', 'thisYM', 'previousYM', 'nextYM'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectedAttendance;
use App\Models\CorrectedBreakTime;

class AdminRequestController extends Controller
{
    // 修正申請承認画面
    public function show(Request $request)
    {
        // 認証ミドルウェアチェック
        $isAdmin = FALSE;
        if (Auth::guard('admin')->check()) {
            $isAdmin = TRUE;
        }

        // 修正申請の情報を取得
        $correctedAttendance = CorrectedAttendance::where('id', $request->attendance_correct_request)->first();
        $correctedBreakTimes = [];
        if (CorrectedBreakTime::where('corrected_attendance_id', $correctedAttendance->id)->count() > 0) {
            // 修正申請の休憩時間が存在する場合は、取得
            $correctedBreakTimes = CorrectedBreakTime::where('corrected_attendance_id', $correctedAttendance->id)->get();
        }
        $user = User::where('id', $correctedAttendance->user_id)->first();

        return view('admin.request.show', compact('isAdmin', 'correctedAttendance', 'correctedBreakTimes', 'user'));
    }

    // 修正申請承認処理
    public function update(Request $request)
    {
        // 修正申請の情報を取得
        $correctedAttendance = CorrectedAttendance::where('id', $request->attendance_correct_request)->first();
        $correctedBreakTimes = CorrectedBreakTime::where('corrected_attendance_id', $request->attendance_correct_request)->get();

        // CorrectedAttendanceのstatusを「承認済み」に更新
        $correctedAttendance->update([
            'status' => '承認済み',
        ]);
        $correctedAttendance->touch();

        // 勤怠情報（出勤時間・退勤時間・休憩開始時間・休憩終了時間）を修正申請された情報に更新
        Attendance::where('id', $correctedAttendance->attendance_id)->first()->update([
            'clock_in' => $correctedAttendance->corrected_clock_in,
            'clock_out' => $correctedAttendance->corrected_clock_out,
        ]);
        Attendance::where('id', $correctedAttendance->attendance_id)->first()->touch();
        foreach ($correctedBreakTimes as $index => $correctedBreakTime) {
            if (($index > 0 and $correctedBreakTimes[$index - 1]->break_time_id === $correctedBreakTimes[$index]->break_time_id) or (count($correctedBreakTimes) === 1)) { //
                // 追加で申請した休憩時間の場合、新規作成
                BreakTime::create([
                    'attendance_id' => $correctedAttendance->attendance_id,
                    'break_start' => $correctedBreakTime->corrected_break_start,
                    'break_end' => $correctedBreakTime->corrected_break_end,
                ]);
            } else {
                // 既存の休憩時間の場合、更新
                BreakTime::where('id', $correctedBreakTime->break_time_id)->first()->update([
                    'break_start' => $correctedBreakTime->corrected_break_start,
                    'break_end' => $correctedBreakTime->corrected_break_end,
                ]);
                BreakTime::where('id', $correctedBreakTime->break_time_id)->first()->touch();
            }
        }

        return redirect()->route('admin.request.show', ['attendance_correct_request' => $request->attendance_correct_request])->with('success', '修正申請の承認処理が完了しました。');
    }
}

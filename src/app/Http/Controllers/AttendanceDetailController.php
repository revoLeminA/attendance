<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CorrectedAttendanceRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectedAttendance;
use App\Models\CorrectedBreakTime;

class AttendanceDetailController extends Controller
{
    // 勤怠詳細画面
    public function show(Request $request)
    {
        // 認証ミドルウェアチェック
        $isAdmin = FALSE;
        if (Auth::guard('admin')->check()) {
            $isAdmin = TRUE;
        }

        // 勤怠情報取得
        $attendance = Attendance::where('id', $request->id)->first();
        $user = User::where('id', $attendance->user_id)->first();
        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

        // 承認申請済みチェック
        $isRequested = false;
        if (CorrectedAttendance::where('attendance_id', $attendance->id)->first() !== null) {
            $isRequested = true;
            $attendance = CorrectedAttendance::where('attendance_id', $attendance->id)->first();
            $breakTimes = CorrectedBreakTime::where('corrected_attendance_id', $attendance->id)->get();
        }

        // 休憩時間nullチェック
        $isBreakTimesNull = false;
        if ($breakTimes->isEmpty()) {
            $isBreakTimesNull = true;
        }

        return view('auth.attendance.show', compact('isAdmin', 'isRequested', 'isBreakTimesNull', 'attendance', 'user', 'breakTimes'));
    }

    // 勤怠修正登録処理
    public function update(CorrectedAttendanceRequest $request)
    {
        // 認証ミドルウェアチェック
        $isAdmin = FALSE;
        if (Auth::guard('admin')->check()) {
            $isAdmin = TRUE;
        }

        $attendance_id = $request->id;
        if ($isAdmin) { // 管理者の場合
            if (CorrectedAttendance::where('id', $request->id)->first() !== null) { // 既に修正申請が存在する場合
                $attendance_id = CorrectedAttendance::where('id', $request->id)->first()->attendance_id;
                // 既存の修正申請を削除
                if (CorrectedBreakTime::where('corrected_attendance_id', $attendance_id)->first() !== null) {
                    CorrectedBreakTime::where('corrected_attendance_id', $attendance_id)->first()->delete();
                }
                CorrectedAttendance::where('id', $request->id)->first()->delete();
            }
            // 勤怠情報を直接更新
            Attendance::where('id', $attendance_id)->first()->update([
                'clock_in' => $request->corrected_clock_in,
                'clock_out' => $request->corrected_clock_out,
            ]);
            Attendance::where('id', $attendance_id)->first()->touch();
            if ($request->break_time_ids !== null) {
                foreach ($request->break_time_ids as $index => $breakTimeId) {
                    BreakTime::where('id', $breakTimeId)->first()->update([
                        'break_start' => $request->corrected_break_starts[$index],
                        'break_end' => $request->corrected_break_ends[$index],
                    ]);
                    BreakTime::where('id', $breakTimeId)->first()->touch();
                }
            }
            // 休憩時間の追加
            if ($request->corrected_break_start_add !== null) {
                BreakTime::create([
                    'attendance_id' => $attendance_id,
                    'break_start' => $request->corrected_break_start_add,
                    'break_end' => $request->corrected_break_end_add,
                ]);
            }
        } else { // 一般ユーザーの場合
            // 勤怠情報（出勤時間・退勤時間・休憩開始時間・休憩終了時間）の修正申請
            $correctAttendance = CorrectedAttendance::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance_id,
                'status' => '承認待ち',
                'corrected_date' => $request->corrected_date,
                'corrected_clock_in' => $request->corrected_clock_in,
                'corrected_clock_out' => $request->corrected_clock_out,
                'corrected_reason' => $request->corrected_reason,
            ]);
            if ($request->break_time_ids !== null) {
                foreach ($request->break_time_ids as $index => $breakTimeId) {
                    CorrectedBreakTime::create([
                        'break_time_id' => $breakTimeId,
                        'corrected_attendance_id' => $correctAttendance->id,
                        'corrected_break_start' => $request->corrected_break_starts[$index],
                        'corrected_break_end' => $request->corrected_break_ends[$index],
                    ]);
                }
            }
            // 休憩時間の追加申請
            if ($request->corrected_break_start_add !== null) {
                CorrectedBreakTime::create([
                    'break_time_id' => is_countable($request->corrected_break_starts) ? $request->break_time_ids[count($request->corrected_break_starts) - 1] : BreakTime::latest('id')->first()->id,
                    'corrected_attendance_id' => $correctAttendance->id,
                    'corrected_break_start' => $request->corrected_break_start_add,
                    'corrected_break_end' => $request->corrected_break_end_add,
                ]);
            }
        }

        return redirect()->route('auth.attendance.show', ['id' => $attendance_id])->with('success', '修正申請をしました。');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminStaffAttendanceController extends Controller
{
    // スタッフ別勤怠一覧画面
    public function index(Request $request)
    {
        // 勤怠を表示するユーザ
        $user = User::where('id', $request->id)->first();

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
            foreach ($rowBreakTimes as $rowBreakTime) {
                if (isset($rowBreakTime->break_end)) {
                    $tmpBreakTime += $rowBreakTime->break_end->diffInMinutes($rowBreakTime->break_start);
                }
            }
            array_push($thisBreakTimes, ['id' => $thisAttendance->id, 'break_time' => round($tmpBreakTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpBreakTime % 60, 2, '0', STR_PAD_LEFT)]);
            // 各勤怠の勤務時間
            if (isset($thisAttendance->clock_out)) {
                $tmpWorkTime = $thisAttendance->clock_out->diffInMinutes($thisAttendance->clock_in) - $tmpBreakTime;
                array_push($thisWorkTimes, ['id' => $thisAttendance->id, 'work_time' => round($tmpWorkTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpWorkTime % 60, 2, '0', STR_PAD_LEFT)]);
            }
        }

        // 前月
        $previousYM = $thisYM->copy()->subMonth();
        // 翌月
        $nextYM = $thisYM->copy()->addMonth();

        return view('admin.staff.attendance.index', compact('user', 'thisDays', 'thisAttendances', 'thisBreakTimes', 'thisWorkTimes', 'thisYM', 'previousYM', 'nextYM'));
    }

    // スタッフ別勤怠CSV出力
    public function export(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $thisYM = Carbon::Create($request->year, $request->month, 01);

        // 出力する年月の勤怠
        $thisAttendances = Attendance::where('user_id', $user->id)->whereBetween('date', [$thisYM->format('Y-m-d'), $thisYM->copy()->endOfMonth()->format('Y-m-d')])->get();

        // CSVファイル名
        $fileName = '勤怠_' . $user->name . '_' . $thisYM->format('Ym') . '.csv';

        // CSVヘッダー
        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];

        // CSVデータ
        $csvData = [];
        foreach ($thisAttendances as $attendance) {
            // 1日の休憩時間を算出
            $rowBreakTimes = BreakTime::where('attendance_id', $attendance->id)->get();
            $tmpBreakTime = 0;
            foreach ($rowBreakTimes as $rowBreakTime) {
                if (isset($rowBreakTime->break_end)) {
                    $tmpBreakTime += $rowBreakTime->break_end->diffInMinutes($rowBreakTime->break_start);
                }
            }
            // CSVデータに追加
            array_push($csvData, [
                'date' => $attendance->date->isoFormat('MM/DD（ddd）'),
                'clock_in' => isset($attendance->clock_in) ? $attendance->clock_in->format('H:i') : '',
                'clock_out' => isset($attendance->clock_out) ? $attendance->clock_out->format('H:i') : '',
                'break_time' => round($tmpBreakTime / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad($tmpBreakTime % 60, 2, '0', STR_PAD_LEFT),
                'work_time' => isset($attendance->clock_out) ? round(($attendance->clock_out->diffInMinutes($attendance->clock_in) - $tmpBreakTime) / 60, 0, PHP_ROUND_HALF_DOWN) . ':' . str_pad(($attendance->clock_out->diffInMinutes($attendance->clock_in) - $tmpBreakTime) % 60, 2, '0', STR_PAD_LEFT) : '',
            ]);
        }

        // CSV出力
        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $handle = fopen('php://output', 'w');
            // CSVヘッダー出力
            fputcsv($handle, $csvHeader);
            // CSVデータ出力
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);

        return $response;
    }
}

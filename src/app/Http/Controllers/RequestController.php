<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CorrectedAttendance;

class RequestController extends Controller
{
    // 申請一覧画面
    public function index(Request $request)
    {
        // 認証ミドルウェアチェック
        $isAdmin = FALSE;
        if (Auth::guard('admin')->check()) {
            $isAdmin = TRUE;
        }

        $isWait = FALSE;
        $isApprove = FALSE;
        $displayedUser = [];
        $corrected_attendances = [];
        if ($isAdmin) { // 管理者の場合
            $displayedUser = User::all();
            if ($request->tab == 'wait') {
                $corrected_attendances = CorrectedAttendance::where('status', '承認待ち')->get();
                $isWait = TRUE;
            } elseif ($request->tab == 'approve') {
                $corrected_attendances = CorrectedAttendance::where('status', '承認済み')->get();
                $isApprove = TRUE;
            }
        } else { // 一般ユーザの場合
            $displayedUser = Auth::user();
            if ($request->tab == 'wait') {
                $corrected_attendances = CorrectedAttendance::where('user_id', $displayedUser->id)->where('status', '承認待ち')->get();
                $isWait = TRUE;
            } elseif ($request->tab == 'approve') {
                $corrected_attendances = CorrectedAttendance::where('user_id', $displayedUser->id)->where('status', '承認済み')->get();
                $isApprove = TRUE;
            }
        }

        return view('auth.request.index', compact('isAdmin', 'isWait', 'isApprove', 'displayedUser', 'corrected_attendances'));
    }
}

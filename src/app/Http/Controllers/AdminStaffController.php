<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    // スタッフ別勤怠一覧画面
    public function index()
    {
        $users = User::all();

        return view('admin.staff.index', compact('users'));
    }
}

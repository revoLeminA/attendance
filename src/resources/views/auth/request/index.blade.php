@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/request/index.css') }}">
@endsection

@section('header-nav')
    <nav class="header-nav">
        <ul class="header-nav__list">
            @if ($isAdmin)
                <li class="header-nav__item"><a href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
                <li class="header-nav__item"><a href="{{ route('admin.staff.index') }}">スタッフ一覧</a></li>
                <li class="header-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'wait']) }}">申請一覧</a></li>
                <li class="header-nav__item">
                    <form class="form" action="/admin/logout" method="post">
                        @csrf
                        <button class="header-nav__btn">ログアウト</button>
                    </form>
                </li>
            @else
                <li class="header-nav__item"><a href="{{ route('user.attendance.create') }}">勤怠</a></li>
                <li class="header-nav__item"><a href="{{ route('user.attendance.index') }}">勤怠一覧</a></li>
                <li class="header-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'wait']) }}">申請</a></li>
                <li class="header-nav__item">
                    <form class="form" action="/logout" method="post">
                        @csrf
                        <button class="header-nav__btn">ログアウト</button>
                    </form>
                </li>
            @endif
        </ul>
    </nav>
@endsection

@section('content')
    <div class="container">
        <div class="container__ttl">
            申請一覧
        </div>

        <nav class="container-nav">
            <ul class="container-nav__list">
                <li class="container-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'wait']) }}"
                        @class(['bold' => $isWait])>承認待ち</a></li>
                <li class="container-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'approve']) }}"
                        @class(['bold' => $isApprove])>承認済み</a></li>
            </ul>
        </nav>

        <table>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @isset($corrected_attendances)
                @foreach ($corrected_attendances as $corrected_attendance)
                    <tr>
                        <!-- 状態 -->
                        <td>{{ $corrected_attendance->status }}</td>
                        <!-- 名前 -->
                        <td>{{ $displayedUser->where('id', $corrected_attendance->user_id)->first()->name }}</td>
                        <!-- 対象日時 -->
                        <td>{{ $corrected_attendance->corrected_date->format('Y/m/d') }}</td>
                        <!-- 申請理由 -->
                        <td>{{ $corrected_attendance->corrected_reason }}</td>
                        <!-- 申請日時 -->
                        <td>{{ $corrected_attendance->updated_at->format('Y/m/d') }}</td>
                        <!-- 詳細 -->
                        @if ($isAdmin)
                            <td><a
                                    href="{{ route('admin.request.show', ['attendance_correct_request' => $corrected_attendance->id]) }}"><b>詳細</b></a>
                            </td>
                        @else
                            <td><a
                                    href="{{ route('auth.attendance.show', ['id' => $corrected_attendance->attendance_id]) }}"><b>詳細</b></a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endisset
        </table>
    </div>
@endsection

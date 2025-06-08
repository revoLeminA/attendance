@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('header-nav')
    <nav class="header-nav">
        <ul class="header-nav__list">
            <li class="header-nav__item"><a href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
            <li class="header-nav__item"><a href="{{ route('admin.staff.index') }}">スタッフ一覧</a></li>
            <li class="header-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'wait']) }}">申請一覧</a></li>
            <li class="header-nav__item">
                <form class="form" action="/admin/logout" method="post">
                    @csrf
                    <button class="header-nav__btn">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
@endsection

@section('content')
    <div class="container">
        <div class="container__ttl">
            {{ $thisYMD->format('Y年m月d日の勤怠') }}
        </div>
        <div class="container__nav">
            <a class="container__nav-prev"
                href="{{ url()->current() . '?year=' . $previousYMD->format('Y') . '&month=' . $previousYMD->format('m') . '&day=' . $previousYMD->format('d') }}">前日</a>
            <div class="container__nav-this">
                {{ $thisYMD->format('Y/m/d') }}
            </div>
            <a class="container__nav-next"
                href="{{ url()->current() . '?year=' . $nextYMD->format('Y') . '&month=' . $nextYMD->format('m') . '&day=' . $nextYMD->format('d') }}">翌日</a>
        </div>

        <table>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @isset($thisAttendances)
                @foreach ($thisAttendances as $thisAttendance)
                    <tr>
                        <!-- 名前 -->
                        <td>{{ $users->where('id', $thisAttendance->user_id)->first()->name }}</td>
                        <!-- 出勤 -->
                        <td>{{ $thisAttendance->clock_in->format('H:i') }}</td>
                        <!-- 退勤 -->
                        @if (isset($thisAttendance->clock_out))
                            <td>{{ $thisAttendance->clock_out->format('H:i') }}</td>
                        @else
                            <td></td>
                        @endif
                        <!-- 休憩 -->
                        @if (
                            !empty($thisBreakTimes) and
                                $thisBreakTimes[array_search($thisAttendance->id, array_column($thisBreakTimes, 'id'))]['id'] ==
                                    $thisAttendance->id)
                            <td>{{ $thisBreakTimes[array_search($thisAttendance->id, array_column($thisBreakTimes, 'id'))]['break_time'] }}
                            </td>
                        @else
                            <td></td>
                        @endif
                        <!-- 合計 -->
                        @if (
                            !empty($thisWorkTimes) and
                                $thisWorkTimes[array_search($thisAttendance->id, array_column($thisWorkTimes, 'id'))]['id'] ==
                                    $thisAttendance->id)
                            <td>{{ $thisWorkTimes[array_search($thisAttendance->id, array_column($thisWorkTimes, 'id'))]['work_time'] }}
                            </td>
                        @else
                            <td></td>
                        @endif
                        <!-- 詳細 -->
                        <td><a href="{{ route('auth.attendance.show', ['id' => $thisAttendance->id]) }}"><b>詳細</b></a></td>
                    </tr>
                @endforeach
            @endisset
        </table>
    </div>
@endsection

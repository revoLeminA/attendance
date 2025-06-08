@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/request/show.css') }}">
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
            勤怠詳細
        </div>

        <form action="{{ route('admin.request.update', ['attendance_correct_request' => $correctedAttendance->id]) }}"
            method="post">
            @method('PATCH')
            @csrf
            <table>
                <tr>
                    <th>名前</th>
                    <td>
                        <span>{{ $user->name }}</span>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <span>{{ $correctedAttendance->corrected_date->format('Y年') }}</span>
                        <span>{{ $correctedAttendance->corrected_date->format('n月j日') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <span>{{ $correctedAttendance->corrected_clock_in->format('H:i') }}</span>
                        <label>～</label>
                        <span>{{ $correctedAttendance->corrected_clock_out->format('H:i') }}</span>
                </tr>
                @if (isset($correctedBreakTimes) && count($correctedBreakTimes) > 0)
                    @foreach ($correctedBreakTimes as $index => $correctedBreakTime)
                        <tr>
                            <th>休憩{{ $index + 1 }}</th>
                            <td>
                                <span>{{ $correctedBreakTime->corrected_break_start->format('H:i') }}</span>
                                <label>～</label>
                                <span>{{ $correctedBreakTime->corrected_break_end->format('H:i') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩{{ $index + 2 }}</th>
                            <td></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <th>休憩1</th>
                        <td></td>
                    </tr>
                @endif
                <tr>
                    <th>備考</th>
                    <td>
                        <span>{{ $correctedAttendance->corrected_reason }}</span>
                    </td>
                </tr>
            </table>
            <div class="form__btn">
                @if ($correctedAttendance->status === '承認待ち')
                    <button class="form__btn-submit" type="submit">承認</button>
                @elseif ($correctedAttendance->status === '承認済み')
                    <button class="form__btn-submitted" disabled>承認済み</button>
                @endif
            </div>
        </form>
    </div>
@endsection

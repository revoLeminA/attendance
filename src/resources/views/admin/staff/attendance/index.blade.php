@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/staff/attendance/index.css') }}">
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
            {{ str_replace(' ', '', $user->name) }}さんの勤怠
        </div>
        <div class="container__nav">
            <a class="container__nav-prev"
                href="{{ url()->current() . '?year=' . $previousYM->format('Y') . '&month=' . $previousYM->format('m') }}">前月</a>
            <div class="container__nav-this">
                {{ $thisYM->format('Y/m') }}
            </div>
            <a class="container__nav-next"
                href="{{ url()->current() . '?year=' . $nextYM->format('Y') . '&month=' . $nextYM->format('m') }}">翌月</a>
        </div>

        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($thisDays as $thisDay)
                <tr>
                    <!-- 日付 -->
                    <td>{{ $thisDay->isoFormat('MM/DD（ddd）') }}</td>
                    <!-- 出勤 -->
                    @if (isset($thisAttendances->where('date', $thisDay)->first()->clock_in))
                        <td>{{ $thisAttendances->where('date', $thisDay)->first()->clock_in->format('H:i') }}</td>
                    @else
                        <td></td>
                    @endif
                    <!-- 退勤 -->
                    @if (isset($thisAttendances->where('date', $thisDay)->first()->clock_out))
                        <td>{{ $thisAttendances->where('date', $thisDay)->first()->clock_out->format('H:i') }}</td>
                    @else
                        <td></td>
                    @endif
                    <!-- 休憩 -->
                    @if (isset($thisAttendances->where('date', $thisDay)->first()->id) and
                            $thisBreakTimes[
                                array_search($thisAttendances->where('date', $thisDay)->first()->id, array_column($thisBreakTimes, 'id'))
                            ]['id'] ==
                                $thisAttendances->where('date', $thisDay)->first()->id)
                        <td>{{ $thisBreakTimes[array_search($thisAttendances->where('date', $thisDay)->first()->id, array_column($thisBreakTimes, 'id'))]['break_time'] }}
                        </td>
                    @else
                        <td></td>
                    @endif
                    <!-- 合計 -->
                    @if (isset($thisAttendances->where('date', $thisDay)->first()->id) and
                            $thisWorkTimes[
                                array_search($thisAttendances->where('date', $thisDay)->first()->id, array_column($thisWorkTimes, 'id'))
                            ]['id'] ==
                                $thisAttendances->where('date', $thisDay)->first()->id)
                        <td>{{ $thisWorkTimes[array_search($thisAttendances->where('date', $thisDay)->first()->id, array_column($thisWorkTimes, 'id'))]['work_time'] }}
                        </td>
                    @else
                        <td></td>
                    @endif
                    <!-- 詳細 -->
                    @if (isset($thisAttendances->where('date', $thisDay)->first()->id))
                        <td><a
                                href="{{ route('auth.attendance.show', ['id' => $thisAttendances->where('date', $thisDay)->first()->id]) }}"><b>詳細</b></a>
                        </td>
                    @else
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>
        <form
            action="{{ route('admin.staff.attendance.csv', ['id' => $user->id, 'year' => $thisYM->format('Y'), 'month' => $thisYM->format('m')]) }}"
            method="post">
            @csrf
            <div class="form__btn">
                <button class="form__btn-submit">CSV出力</button>
            </div>
        </form>
    </div>
@endsection

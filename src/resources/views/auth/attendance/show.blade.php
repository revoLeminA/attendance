@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/attendance/show.css') }}">
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
            勤怠詳細
        </div>

        <form action="{{ route('auth.attendance.update', ['id' => $attendance->id]) }}" method="post">
            @method('PATCH')
            @csrf
            <table>
                <tr>
                    <th>名前</th>
                    <td>
                        <span>{{ $user->name }}</span>
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        @if ($isRequested)
                            <span>{{ $attendance->corrected_date->format('Y年') }}</span>
                            <span>{{ $attendance->corrected_date->format('n月j日') }}</span>
                            <input type="hidden" name="corrected_date" value="{{ $attendance->corrected_date }}">
                        @else
                            <span>{{ $attendance->date->format('Y年') }}</span>
                            <span>{{ $attendance->date->format('n月j日') }}</span>
                            <input type="hidden" name="corrected_date" value="{{ $attendance->date }}">
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        @if ($isAdmin)
                            @if ($isRequested)
                                <input type="time" name="corrected_clock_in"
                                    value="{{ old('corrected_clock_in') ?? $attendance->corrected_clock_in->format('H:i') }}">
                            @elseif ($attendance->clock_in)
                                <input type="time" name="corrected_clock_in"
                                    value="{{ old('corrected_clock_in') ?? $attendance->clock_in->format('H:i') }}">
                            @else
                                <input type="time" name="corrected_clock_in" value="{{ old('corrected_clock_in') }}">
                            @endif
                        @else
                            @if ($isRequested)
                                <span>{{ old('corrected_clock_in') ?? $attendance->corrected_clock_in->format('H:i') }}</span>
                            @elseif ($attendance->clock_in)
                                <input type="time" name="corrected_clock_in"
                                    value="{{ old('corrected_clock_in') ?? $attendance->clock_in->format('H:i') }}">
                            @else
                                <input type="time" name="corrected_clock_in" value="{{ old('corrected_clock_in') }}">
                            @endif
                        @endif
                        <label for="">～</label>
                        @if ($isAdmin)
                            @if ($isRequested)
                                <input type="time" name="corrected_clock_out"
                                    value="{{ old('corrected_clock_out') ?? $attendance->corrected_clock_out->format('H:i') }}">
                            @elseif ($attendance->clock_out)
                                <input type="time" name="corrected_clock_out"
                                    value="{{ old('corrected_clock_out') ?? $attendance->clock_out->format('H:i') }}">
                            @else
                                <input type="time" name="corrected_clock_out"
                                    value="{{ old('corrected_clock_out') }}">
                            @endif
                        @else
                            @if ($isRequested)
                                <span>{{ old('corrected_clock_out') ?? $attendance->corrected_clock_out->format('H:i') }}</span>
                            @elseif ($attendance->clock_out)
                                <input type="time" name="corrected_clock_out"
                                    value="{{ old('corrected_clock_out') ?? $attendance->clock_out->format('H:i') }}">
                            @else
                                <input type="time" name="corrected_clock_out" value="{{ old('corrected_clock_out') }}">
                            @endif
                        @endif
                        <div class="form__item-error">
                            @error('corrected_clock_in')
                                {{ $message }}
                            @enderror
                            @error('corrected_clock_out')
                                {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                @if (!$isBreakTimesNull)
                    @foreach ($breakTimes as $index => $breakTime)
                        <tr>
                            <th>休憩{{ $index + 1 }}</th>
                            <td>
                                @if ($isAdmin)
                                    @if ($isRequested)
                                        <input type="time" name="corrected_break_starts[]"
                                            value="{{ old('corrected_break_starts.' . $index) ?? $breakTime->corrected_break_start->format('H:i') }}">
                                    @else
                                        @if ($breakTime->break_start)
                                            <input type="time" name="corrected_break_starts[]"
                                                value="{{ old('corrected_break_starts.' . $index) ?? $breakTime->break_start->format('H:i') }}">
                                        @else
                                            <input type="time" name="corrected_break_starts[]"
                                                value="{{ old('corrected_break_starts.' . $index) }}">
                                        @endif
                                    @endif
                                @else
                                    @if ($isRequested)
                                        <span>{{ old('corrected_break_starts.' . $index) ?? $breakTime->corrected_break_start->format('H:i') }}</span>
                                    @else
                                        @if ($breakTime->break_start)
                                            <input type="time" name="corrected_break_starts[]"
                                                value="{{ old('corrected_break_starts.' . $index) ?? $breakTime->break_start->format('H:i') }}">
                                        @else
                                            <input type="time" name="corrected_break_starts[]"
                                                value="{{ old('corrected_break_starts.' . $index) }}">
                                        @endif
                                    @endif
                                @endif
                                <label for="">～</label>
                                @if ($isAdmin)
                                    @if ($isRequested)
                                        <input type="time" name="corrected_break_ends[]"
                                            value="{{ old('corrected_break_ends.' . $index) ?? $breakTime->corrected_break_end->format('H:i') }}">
                                    @else
                                        @if ($breakTime->break_end)
                                            <input type="time" name="corrected_break_ends[]"
                                                value="{{ old('corrected_break_ends.' . $index) ?? $breakTime->break_end->format('H:i') }}">
                                        @else
                                            <input type="time" name="corrected_break_ends[]"
                                                value="{{ old('corrected_break_ends.' . $index) }}">
                                        @endif
                                        <input type="hidden" name="break_time_ids[]" value="{{ $breakTime->id }}">
                                    @endif
                                @else
                                    @if ($isRequested)
                                        <span>{{ old('corrected_break_ends.' . $index) ?? $breakTime->corrected_break_end->format('H:i') }}</span>
                                    @else
                                        @if ($breakTime->break_end)
                                            <input type="time" name="corrected_break_ends[]"
                                                value="{{ old('corrected_break_ends.' . $index) ?? $breakTime->break_end->format('H:i') }}">
                                        @else
                                            <input type="time" name="corrected_break_ends[]"
                                                value="{{ old('corrected_break_ends.' . $index) }}">
                                        @endif
                                        <input type="hidden" name="break_time_ids[]" value="{{ $breakTime->id }}">
                                    @endif
                                @endif
                                <div class="form__item-error">
                                    @error('corrected_break_starts.' . $index)
                                        {{ $message }}
                                    @enderror
                                    @error('corrected_break_ends.' . $index)
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>休憩{{ $index + 2 }}</th>
                        <td>
                            @if ($isAdmin)
                                @if ($isRequested)
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @else
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @endif
                            @else
                                @if ($isRequested)
                                    <span>{{ old('corrected_break_start_add') }}</span>
                                    <label for="">～</label>
                                    <span>{{ old('corrected_break_end_add') }}</span>
                                @else
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @endif
                            @endif
                            <div class="form__item-error">
                                @error('corrected_break_start_add')
                                    {{ $message }}
                                @enderror
                                @error('corrected_break_end_add')
                                    {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                @else
                    <tr>
                        <th>休憩1</th>
                        <td>
                            @if ($isAdmin)
                                @if ($isRequested)
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @else
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @endif
                            @else
                                @if ($isRequested)
                                    <span>{{ old('corrected_break_start_add') }}</span>
                                    <label for="">～</label>
                                    <span>{{ old('corrected_break_end_add') }}</span>
                                @else
                                    <input type="time" name="corrected_break_start_add"
                                        value="{{ old('corrected_break_start_add') }}">
                                    <label for="">～</label>
                                    <input type="time" name="corrected_break_end_add"
                                        value="{{ old('corrected_break_end_add') }}">
                                @endif
                            @endif
                            <div class="form__item-error">
                                @error('corrected_break_start_add')
                                    {{ $message }}
                                @enderror
                                @error('corrected_break_end_add')
                                    {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <th>備考</th>
                    <td>
                        @if ($isAdmin)
                            @if ($isRequested)
                                <textarea name="corrected_reason">{{ old('corrected_reason') ?? $attendance->corrected_reason }}</textarea>
                            @else
                                <textarea name="corrected_reason">{{ old('corrected_reason') }}</textarea>
                            @endif
                        @else
                            @if ($isRequested)
                                <p>{{ $attendance->corrected_reason }}</p>
                            @else
                                <textarea name="corrected_reason">{{ old('corrected_reason') }}</textarea>
                            @endif
                        @endif
                        <div class="form__item-error">
                            @error('corrected_reason')
                                {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            @if (!$isAdmin and $isRequested)
                @if ($attendance->status === '承認待ち')
                    <p class="form__btn-deleted">*承認待ちのため修正はできません。</p>
                @elseif ($attendance->status === '承認済み')
                    <p class="form__btn-deleted">*承認済みのため修正はできません。</p>
                @endif
            @else
                <div class="form__btn">
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    <button class="form__btn-submit" type="submit">修正</button>
                </div>
            @endif
        </form>
    </div>
@endsection

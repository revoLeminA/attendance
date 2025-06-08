@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user/attendance/create.css') }}">
@endsection

@section('header-nav')
    <nav class="header-nav">
        <ul class="header-nav__list">
            <li class="header-nav__item"><a href="{{ route('user.attendance.create') }}">勤怠</a></li>
            <li class="header-nav__item"><a href="{{ route('user.attendance.index') }}">勤怠一覧</a></li>
            <li class="header-nav__item"><a href="{{ route('auth.request.index', ['tab' => 'wait']) }}">申請</a></li>
            <li class="header-nav__item">
                <form class="form" action="/logout" method="post">
                    @csrf
                    <button class="header-nav__btn">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
@endsection

@section('content')
    <div class="container">
        <p class="current-status">{{ $status }}</p>
        <p id="real-current-date" class="current-date">{{ $currentDate }}</p>
        <p id="real-current-time" class="current-time">{{ $currentTime }}</p>
        @if ($status === '勤務外')
            <form action="{{ route('user.attendance.store') }}" method="post">
                @csrf
                <div class="form__btn">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="pushed_at" id="start-button">
                    <button class="form__btn-submit" onclick="setCurrentTime('start')">出勤</button>
                </div>
            </form>
        @elseif ($status === '勤務中')
            <div class="forms-group">
                <form action="{{ route('user.attendance.update') }}" method="post">
                    @method('PATCH')
                    @csrf
                    <div class="form__btn">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="pushed_at" id="end-button">
                        <button class="form__btn-submit" onclick="setCurrentTime('end')">退勤</button>
                    </div>
                </form>
                <form action="{{ route('user.attendance.store') }}" method="post">
                    @csrf
                    <div class="form__btn">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="pushed_at" id="start-button">
                        <button class="form__btn-submit reverse" onclick="setCurrentTime('start')">休憩入</button>
                    </div>
                </form>
            </div>
        @elseif ($status === '休憩中')
            <form action="{{ route('user.attendance.update') }}" method="post">
                @method('PATCH')
                @csrf
                <div class="form__btn">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="pushed_at" id="end-button">
                    <button class="form__btn-submit reverse" onclick="setCurrentTime('end')">休憩戻</button>
                </div>
            </form>
        @elseif ($status === '退勤済')
            <p class="fin-msg">お疲れ様でした。</p>
        @endif
    </div>
@endsection

@push('script')
    <script src="{{ asset('js/user/attendance/create.js') }}"></script>
@endpush

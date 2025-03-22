@extends('layout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/header_nav.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('header_nav')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li class="header-nav__item"><a href="{{ route('user.attendance.create') }}">勤怠</a></li>
        <li class="header-nav__item"><a href="{{ route('user.attendance.index') }}">勤怠一覧</a></li>
        <li class="header-nav__item"><a href="{{ route('auth.request.index') }}">申請</a></li>
        <li class="header-nav__item">
            <form class="form" action="/logout" method="post">
                @csrf
                <button class="header-nav__btn">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection
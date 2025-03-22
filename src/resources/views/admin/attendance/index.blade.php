@extends('layout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/header_nav.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('header_nav')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li class="header-nav__item"><a href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
        <li class="header-nav__item"><a href="{{ route('admin.staff.index') }}">スタッフ一覧</a></li>
        <li class="header-nav__item"><a href="{{ route('auth.request.index') }}">申請一覧</a></li>
        <li class="header-nav__item">
            <form class="form" action="/admin/logout" method="post">
                @csrf
                <button class="header-nav__btn">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection
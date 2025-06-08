@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
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
            スタッフ一覧
        </div>
        <table>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.staff.attendance.index', ['id' => $user->id]) }}"><b>詳細</b></a></td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection

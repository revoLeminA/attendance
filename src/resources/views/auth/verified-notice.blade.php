@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="container__ttl hide">ダミータイトル</div>
        <div class="container__txt">
            <p>登録していただいたメールアドレスの確認が完了しました。</p>
            <p>元の画面から認証を完了してください。</p>
        </div>
    </div>
@endsection

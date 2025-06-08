@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/header/nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('header-nav')
    <nav class="header-nav">
        <ul class="header-nav__list">
            <li class="header-nav__item">
                <form class="form" action="/logout" method="post">
                    @csrf
                    <button class="header-nav__btn hide">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
@endsection

@section('content')
    <div class="container">
        <div class="container__ttl hide">ダミータイトル</div>
        <div class="container__txt">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        </div>
        <form class="form" action="{{ route('verification.notice') }}" method="get">
            <div class="form__btn">
                <button class="form__btn-submit verify" type="submit">認証はこちらから</button>
            </div>
        </form>
        <form class="form" action="{{ route('verification.send') }}" method="post">
            @csrf
            <div class="form__btn">
                <button class="form__btn-submit link" type="submit">認証メールを再送する</button>
            </div>
        </form>
    </div>
@endsection

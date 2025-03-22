@extends('layout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="container__ttl">管理者ログイン</div>
    <form class="login-form" action="/admin/login" method="post">
        @csrf
        <div class="form__item">
            <label class="form__item-ttl" for="email">メールアドレス</label>
            <input type="text" class="form__item-input" name="email" value="{{ old('email') }}">
            <div class="form__item-error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__item">
            <label class="form__item-ttl" for="password">パスワード</label>
            <input type="password" class="form__item-input" name="password" value="{{ old('password') }}">
            <div class="form__item-error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__btn">
            <button class="form__btn-submit" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection
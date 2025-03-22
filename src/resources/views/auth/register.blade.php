@extends('layout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="container__ttl">会員登録</div>
    <form class="create-form" action="/register" method="post">
        @csrf
        <div class="form__item">
            <label class="form__item-ttl" for="user_name">名前</label>
            <input type="text" class="form__item-input" name="user_name" value="{{ old('user_name') }}">
            <div class="form__item-error">
                @error('name')
                    {{ $message }}
                @enderror
            </div>
        </div>
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
        <div class="form__item">
            <label class="form__item-ttl" for="password">確認用パスワード</label>
            <input type="password" class="form__item-input" name="password_confirmation" value="{{ old('password_confirmation') }}">
            <div class="form__item-error">
                @error('password_confirmation')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__btn">
            <button class="form__btn-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="btm__nav">
        <a href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
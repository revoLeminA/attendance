<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header>
        <div class="header-inner">
            <div class="header__ttl">
                <img src="{{ asset('images/logo.svg') }}">
            </div>
            @yield('header-nav')
        </div>
        <div class="attendance__alert">
            @if (session('success'))
                <div class="attendance__alert--success">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="attendance__alert--error">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>

</body>

@stack('script')

</html>

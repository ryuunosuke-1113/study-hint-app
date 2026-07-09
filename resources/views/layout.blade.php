<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>受験ヒントメモ</title>
    <link rel="stylesheet" href="/css/style.css?v=1">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#8b6f47">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ヒントメモ">
</head>

<body>
    <header>
        <h1>受験ヒントメモ</h1>

        <nav>
            <a href="{{ route('study-hints.index') }}">ヒント一覧</a>
            |
            <a href="{{ route('subjects.index') }}">科目管理</a>
            |
            <a href="{{ route('books.index') }}">参考書管理</a>
        </nav>
        <hr>
    </header>
    <main>
        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>

</html>

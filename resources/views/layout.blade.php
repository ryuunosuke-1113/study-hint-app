<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>受験ヒントメモ</title>
    <link rel="stylesheet" href="/css/style.css?v=1">
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
</body>

</html>

@extends('layout')

@section('content')
    <h2>参考書管理</h2>

    <a href="{{ route('books.create') }}">＋参考書を追加</a>
    <br><br>

    @foreach ($books as $book)
        <div style="border:1px solid #ccc; padding:12px; margin-bottom:10px;">
            <strong>{{ $book->subject->name }}</strong>
            <br>
            {{ $book->name }}

            <div style="margin-top:8px;">
                <a href="{{ route('books.edit', $book) }}">編集</a>

                <form action="{{ route('books.destroy', $book) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('削除しますか？')">
                        削除
                    </button>
                </form>
            </div>
        </div>
    @endforeach
@endsection

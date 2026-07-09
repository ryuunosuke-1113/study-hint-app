@extends('layout')

@section('content')
    <h2>科目管理</h2>

    <a href="{{ route('subjects.create') }}">＋科目を追加</a>
    <br><br>

    @foreach ($subjects as $subject)
        <div style="border:1px solid #ccc; padding:12px; margin-bottom:10px;">
            <strong>{{ $subject->name }}</strong>

            <div style="margin-top:8px;">
                <a href="{{ route('subjects.edit', $subject) }}">編集</a>

                <form action="{{ route('subjects.destroy', $subject, false) }}" method="POST">
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

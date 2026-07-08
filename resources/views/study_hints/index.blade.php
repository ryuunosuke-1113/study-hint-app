@extends('layout')

@section('content')
    <a href="{{ route('study-hints.create') }}">＋ヒントを追加</a>
    <div class="card">

        <form method="GET">

            <label>科目</label>

            <select name="subject_id" id="subject_id">

                <option value="">すべて</option>

                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                        {{ $subject->name }}

                    </option>
                @endforeach

            </select>

            <label>参考書</label>

            <select name="book_id" id="book_id">

                <option value="">すべて</option>

                @foreach ($books as $book)
                    <option value="{{ $book->id }}" data-subject-id="{{ $book->subject_id }}"
                        {{ request('book_id') == $book->id ? 'selected' : '' }}>

                        {{ $book->name }}

                    </option>
                @endforeach

            </select>

            <label>ページ</label>

            <input type="number" name="page_number" value="{{ request('page_number') }}">

            <label>大問</label>

            <input type="text" name="question_no" maxlength="1" value="{{ request('question_no') }}">

            <label>ヒント</label>

            <input type="text" name="hint" value="{{ request('hint') }}">

            <button class="btn">検索</button>

            <a class="btn btn-secondary" href="{{ route('study-hints.index') }}">

                クリア

            </a>

        </form>

    </div>

    @foreach ($studyHints as $studyHint)
        <div style="border:1px solid #ccc; padding:15px; margin:15px 0;">
            <h2>
                {{ $studyHint->book->subject->name }} / {{ $studyHint->book->name }}
            </h2>

            <p>
                P.{{ $studyHint->page_number }}
                @if ($studyHint->question_no_1)
                    - {{ $studyHint->question_no_1 }}
                @endif
                @if ($studyHint->question_no_2)
                    - {{ $studyHint->question_no_2 }}
                @endif
                @if ($studyHint->question_no_3)
                    - {{ $studyHint->question_no_3 }}
                @endif
            </p>

            <p>{{ $studyHint->hint }}</p>

            <a href="{{ route('study-hints.edit', $studyHint) }}">編集</a>

            <form action="{{ route('study-hints.destroy', $studyHint) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('削除しますか？')">
                    削除
                </button>
            </form>
        </div>
    @endforeach
@endsection

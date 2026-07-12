@extends('layout')

@section('content')
    <a href="{{ route('study-hints.create') }}">
        ＋ヒントを追加
    </a>

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

            <input type="text" name="question_no" maxlength="4" value="{{ request('question_no') }}">

            <label>ヒント</label>

            <input type="text" name="hint" value="{{ request('hint') }}">

            <button class="btn">
                検索
            </button>

            <a class="btn btn-secondary" href="{{ route('study-hints.index') }}">
                クリア
            </a>
        </form>
    </div>

    @forelse ($studyHints as $studyHint)
        <div style="border:1px solid #ccc; padding:15px; margin:15px 0;">
            <h2>
                {{ $studyHint->book->subject->name }}
                /
                {{ $studyHint->book->name }}
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

            @php
                $displayHints = $studyHint->problemHints;
            @endphp
            <div class="problem-hints">
                @forelse ($displayHints as $problemHint)
                    @php
                        $targetId = 'problem-hint-' . $studyHint->id . '-' . $problemHint->hint_order;
                    @endphp

                    <div class="problem-hint-item">
                        <button type="button" class="hint-toggle-button" data-target="{{ $targetId }}"
                            data-hint-number="{{ $problemHint->hint_order }}" aria-expanded="false">
                            ヒント{{ $problemHint->hint_order }}を表示
                        </button>

                        <div id="{{ $targetId }}" class="problem-hint-content" hidden>
                            @if (filled($problemHint->content))
                                <p>
                                    {!! nl2br(e($problemHint->content)) !!}
                                </p>
                            @endif

                            @if (filled($problemHint->image_url))
                                <div class="hint-image-wrapper">
                                    <img src="{{ $problemHint->image_url }}" alt="ヒント{{ $problemHint->hint_order }}の画像"
                                        class="hint-thumbnail" data-full-image="{{ $problemHint->image_url }}">
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p>ヒントは登録されていません。</p>
                @endforelse
            </div>

            <div class="study-hint-actions">
                <a href="{{ route('study-hints.edit', $studyHint) }}">
                    編集
                </a>

                <form action="{{ route('study-hints.destroy', $studyHint) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')

                    <button type="submit" onclick="return confirm('削除しますか？')">
                        削除
                    </button>
                </form>
            </div>
        </div>
    @empty
        <p>該当するヒントはありません。</p>
    @endforelse

    {{-- ライトボックスはページ内に1つだけ置きます --}}
    <div id="image-lightbox" class="image-lightbox" aria-hidden="true">
        <button type="button" id="lightbox-close" class="lightbox-close" aria-label="画像を閉じる">
            ×
        </button>

        <img id="lightbox-image" class="lightbox-image" src="" alt="拡大表示">
    </div>

@endsection

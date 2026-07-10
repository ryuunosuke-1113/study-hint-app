@extends('layout')

@section('content')
    <h2>ヒント編集</h2>

    @if (!empty($studyHint?->image_url))
        <img src="{{ $studyHint->image_url }}" alt="現在の画像1" class="hint-thumbnail">
    @endif

    @if (!empty($studyHint?->image_url_2))
        <img src="{{ $studyHint->image_url_2 }}" alt="現在の画像2" class="hint-thumbnail">
    @endif

    <form action="{{ route('study-hints.update', ['study_hint' => $studyHint->id]) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('study_hints.form')

        <button type="submit">更新する</button>
    </form>

    <a href="{{ route('study-hints.index') }}">戻る</a>
@endsection

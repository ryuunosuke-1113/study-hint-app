@extends('layout')

@section('content')
    <h2>ヒント編集</h2>

    <form action="{{ route('study-hints.update', $studyHint) }}" method="POST">
        @csrf
        @method('PUT')

        @include('study_hints.form')

        <button type="submit">更新する</button>
    </form>

    <a href="{{ route('study-hints.index') }}">戻る</a>
@endsection

@extends('layout')

@section('content')
    <h2>ヒント追加</h2>

    <form id="study-hint-form" action="{{ route('study-hints.store') }}" method="POST" enctype="multipart/form-data"> @csrf

        @include('study_hints.form')

        <button type="submit">登録する</button>
    </form>

    <a href="{{ route('study-hints.index') }}">戻る</a>
@endsection

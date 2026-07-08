@extends('layout')

@section('content')
    <h2>参考書追加</h2>

    <form action="{{ route('books.store') }}" method="POST">
        @csrf

        @include('books.form')

        <button type="submit">登録する</button>
    </form>

    <br>
    <a href="{{ route('books.index') }}">戻る</a>
@endsection

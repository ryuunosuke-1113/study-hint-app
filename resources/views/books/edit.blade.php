@extends('layout')

@section('content')
    <h2>参考書編集</h2>

    <form action="{{ route('books.update', $book) }}" method="POST">
        @csrf
        @method('PUT')

        @include('books.form')

        <button type="submit">更新する</button>
    </form>

    <br>
    <a href="{{ route('books.index') }}">戻る</a>
@endsection

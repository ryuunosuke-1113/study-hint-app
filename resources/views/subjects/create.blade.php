@extends('layout')

@section('content')
    <h2>科目追加</h2>

    <form action="{{ route('subjects.store') }}" method="POST">
        @csrf

        <label>科目名</label>
        <input type="text" name="name" value="{{ old('name') }}">

        @error('name')
            <p style="color:red;">{{ $message }}</p>
        @enderror

        <br><br>

        <button type="submit">登録する</button>
    </form>

    <br>
    <a href="{{ route('subjects.index') }}">戻る</a>
@endsection

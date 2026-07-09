@extends('layout')

@section('content')
    <h2>科目編集</h2>

    <form action="{{ route('subjects.update', $subject, false) }}" method="POST">
        @csrf
        @method('PUT')

        <label>科目名</label>
        <input type="text" name="name" value="{{ old('name', $subject->name) }}">

        @error('name')
            <p style="color:red;">{{ $message }}</p>
        @enderror

        <br><br>

        <button type="submit">更新する</button>
    </form>

    <br>
    <a href="{{ route('subjects.index') }}">戻る</a>
@endsection

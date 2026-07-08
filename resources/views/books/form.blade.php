<label>科目</label>
<select name="subject_id">
    <option value="">選択してください</option>
    @foreach ($subjects as $subject)
        <option value="{{ $subject->id }}"
            {{ old('subject_id', $book->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
            {{ $subject->name }}
        </option>
    @endforeach
</select>

@error('subject_id')
    <p style="color:red;">{{ $message }}</p>
@enderror

<br><br>

<label>参考書名</label>
<input type="text" name="name" value="{{ old('name', $book->name ?? '') }}">

@error('name')
    <p style="color:red;">{{ $message }}</p>
@enderror

<br><br>

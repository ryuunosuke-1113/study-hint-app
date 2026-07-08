<label>科目</label>
<select name="subject_id" id="subject_id">
    <option value="">選択してください</option>
    @foreach ($subjects as $subject)
        <option value="{{ $subject->id }}"
            {{ old('subject_id', $studyHint->book->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
            {{ $subject->name }}
        </option>
    @endforeach
</select>

<br><br>

<label>参考書</label>
<select name="book_id" id="book_id">
    <option value="">選択してください</option>
    @foreach ($books as $book)
        <option value="{{ $book->id }}" data-subject-id="{{ $book->subject_id }}"
            {{ old('book_id', $studyHint->book_id ?? '') == $book->id ? 'selected' : '' }}>
            {{ $book->name }}
        </option>
    @endforeach
</select>

@error('book_id')
    <p style="color:red;">{{ $message }}</p>
@enderror

<br><br>
<label>ページ番号</label>
<input type="number" name="page_number" value="{{ old('page_number', $studyHint->page_number ?? '') }}">
@error('page_number')
    <p style="color:red;">{{ $message }}</p>
@enderror

<label>大問番号1</label>
<input type="text" name="question_no_1" maxlength="1"
    value="{{ old('question_no_1', $studyHint->question_no_1 ?? '') }}">
@error('question_no_1')
    <p style="color:red;">{{ $message }}</p>
@enderror

<label>大問番号2</label>
<input type="text" name="question_no_2" maxlength="1"
    value="{{ old('question_no_2', $studyHint->question_no_2 ?? '') }}">
@error('question_no_2')
    <p style="color:red;">{{ $message }}</p>
@enderror

<label>大問番号3</label>
<input type="text" name="question_no_3" maxlength="1"
    value="{{ old('question_no_3', $studyHint->question_no_3 ?? '') }}">
@error('question_no_3')
    <p style="color:red;">{{ $message }}</p>
@enderror

<label>ヒント内容</label>
<textarea name="hint">{{ old('hint', $studyHint->hint ?? '') }}</textarea>
@error('hint')
    <p style="color:red;">{{ $message }}</p>
@enderror
<script>
    const subjectSelect = document.getElementById('subject_id');
    const bookSelect = document.getElementById('book_id');
    const allBookOptions = Array.from(bookSelect.options);

    function filterBooks(resetBook = false) {
        const selectedSubjectId = subjectSelect.value;
        const currentBookId = bookSelect.value;

        bookSelect.innerHTML = '';

        allBookOptions.forEach(option => {
            if (option.value === '') {
                bookSelect.appendChild(option);
                return;
            }

            if (option.dataset.subjectId === selectedSubjectId) {
                bookSelect.appendChild(option);
            }
        });

        if (resetBook) {
            bookSelect.value = '';
        } else {
            bookSelect.value = currentBookId;
        }
    }

    filterBooks(false);

    subjectSelect.addEventListener('change', function() {
        filterBooks(true);
    });
</script>

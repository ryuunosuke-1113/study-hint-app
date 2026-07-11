<label>
    科目</label>
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

<br><br>

<br><br>

<label for="image">画像添付</label>

@if (!empty($studyHint?->image_url))
    <p>現在の画像が登録されています。</p>

    <input type="hidden" name="current_image" value="{{ $studyHint->image_url }}">
@endif

<input type="file" name="image" id="image" accept="image/*">

<p>
    画像は1枚まで登録できます。
</p>

@error('image')
    <p style="color:red;">{{ $message }}</p>
@enderror
@error('images.*')
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
    const pasteArea = document.getElementById('image-paste-area');
    const imageInput = document.getElementById('images');
    const pasteStatus = document.getElementById('paste-status');

    let selectedImages = Array.from(imageInput.files ?? []);

    function syncImageInput() {
        const dataTransfer = new DataTransfer();

        selectedImages.forEach(function(file) {
            dataTransfer.items.add(file);
        });

        imageInput.files = dataTransfer.files;
    }

    function updateImageStatus() {
        if (selectedImages.length === 0) {
            pasteStatus.hidden = true;
            return;
        }

        pasteStatus.textContent =
            `画像を${selectedImages.length}枚設定しました。`;

        pasteStatus.hidden = false;
        pasteArea.classList.add('has-image');
    }

    imageInput.addEventListener('change', function() {
        const files = Array.from(imageInput.files);

        if (files.length > 2) {
            selectedImages = [];
            imageInput.value = '';

            pasteStatus.textContent =
                '画像は最大2枚まで登録できます。';

            pasteStatus.hidden = false;
            return;
        }

        selectedImages = files;
        updateImageStatus();
    });

    pasteArea.addEventListener('paste', function(event) {
        event.preventDefault();

        if (selectedImages.length >= 2) {
            pasteStatus.textContent =
                '画像は最大2枚まで登録できます。';

            pasteStatus.hidden = false;
            return;
        }

        const clipboardData = event.clipboardData;
        let pastedImage = null;

        if (clipboardData?.files) {
            for (const file of clipboardData.files) {
                if (file.type.startsWith('image/')) {
                    pastedImage = file;
                    break;
                }
            }
        }

        if (!pastedImage && clipboardData?.items) {
            for (const item of clipboardData.items) {
                if (
                    item.kind === 'file' &&
                    item.type.startsWith('image/')
                ) {
                    pastedImage = item.getAsFile();
                    break;
                }
            }
        }

        if (!pastedImage) {
            pasteStatus.textContent =
                'クリップボードから画像を取得できませんでした。';

            pasteStatus.hidden = false;
            return;
        }

        try {
            selectedImages.push(pastedImage);
            syncImageInput();
            updateImageStatus();

            pasteStatus.textContent =
                `画像を貼り付けました。現在${selectedImages.length}枚です。`;
        } catch (error) {
            console.error(error);

            selectedImages.pop();

            pasteStatus.textContent =
                '貼り付け画像をファイル欄へ設定できませんでした。';
            pasteStatus.hidden = false;
        }
    });
    try {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(imageFile);
        imageInput.files = dataTransfer.files;

        if (imageInput.files.length === 0) {
            throw new Error('ファイル入力へ画像を設定できませんでした。');
        }

        pasteStatus.textContent =
            `画像を貼り付けました：${imageFile.name || 'clipboard-image.png'}`;
        pasteStatus.hidden = false;

        pasteArea.textContent = '画像を貼り付け済みです';
        pasteArea.classList.add('has-image');

        console.log('貼り付け画像:', imageInput.files[0]);
    } catch (error) {
        console.error(error);

        pasteStatus.textContent =
            'この端末では貼り付け画像を送信できません。下のファイル選択を利用してください。';
        pasteStatus.hidden = false;
    }
    });
    const studyHintForm = document.getElementById('study-hint-form');

    if (studyHintForm) {
        studyHintForm.addEventListener('submit', function() {
            console.log('送信する画像数:', imageInput.files.length);
            console.log('送信する画像:', imageInput.files[0] ?? null);
        });
    }
    const studyHintForm = document.getElementById('study-hint-form');

    if (studyHintForm) {
        studyHintForm.addEventListener('submit', function() {
            console.log(
                '送信画像数:',
                imageInput.files.length
            );
        });
    }
</script>

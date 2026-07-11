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

<div id="image-paste-area" class="image-paste-area" contenteditable="true" role="textbox" aria-label="画像貼り付け欄">
    ここをタップまたはクリックして、スクリーンショットを貼り付けてください
</div>

<p id="paste-status" class="paste-status" hidden></p>

<input type="file" name="image" id="image" accept="image/*">

<p>画像は1枚まで登録できます。</p>

@error('image')
    <p style="color:red;">{{ $message }}</p>
@enderror
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /*
         * 科目に応じて参考書を絞り込む処理
         */
        const subjectSelect = document.getElementById('subject_id');
        const bookSelect = document.getElementById('book_id');

        if (subjectSelect && bookSelect) {
            const allBookOptions = Array.from(bookSelect.options);

            function filterBooks(resetBook = false) {
                const selectedSubjectId = subjectSelect.value;
                const currentBookId = bookSelect.value;

                bookSelect.innerHTML = '';

                allBookOptions.forEach(function(option) {
                    if (
                        option.value === '' ||
                        option.dataset.subjectId === selectedSubjectId
                    ) {
                        bookSelect.appendChild(option);
                    }
                });

                bookSelect.value = resetBook ? '' : currentBookId;
            }

            filterBooks(false);

            subjectSelect.addEventListener('change', function() {
                filterBooks(true);
            });
        }

        /*
         * 画像1枚のファイル選択・クリップボード貼り付け処理
         */
        const pasteArea = document.getElementById('image-paste-area');
        const imageInput = document.getElementById('image');
        const pasteStatus = document.getElementById('paste-status');

        if (!pasteArea || !imageInput || !pasteStatus) {
            return;
        }

        function showStatus(message, isError = false) {
            pasteStatus.textContent = message;
            pasteStatus.hidden = false;
            pasteStatus.style.color = isError ? 'red' : '#267326';
        }

        function setImageFile(imageFile) {
            if (!imageFile || !imageFile.type.startsWith('image/')) {
                showStatus('画像ファイルを取得できませんでした。', true);
                return;
            }

            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(imageFile);
                imageInput.files = dataTransfer.files;

                if (imageInput.files.length !== 1) {
                    throw new Error(
                        '画像をファイル入力へ設定できませんでした。'
                    );
                }

                pasteArea.textContent = '画像を貼り付け済みです';
                pasteArea.classList.add('has-image');

                showStatus(
                    '画像を貼り付けました。登録ボタンを押してください。'
                );
            } catch (error) {
                console.error(error);

                showStatus(
                    'この端末では貼り付け画像を送信できません。下のファイル選択を利用してください。',
                    true
                );
            }
        }

        pasteArea.addEventListener('paste', function(event) {
            event.preventDefault();

            const clipboardData = event.clipboardData;
            let imageFile = null;

            if (clipboardData?.files) {
                for (const file of clipboardData.files) {
                    if (file.type.startsWith('image/')) {
                        imageFile = file;
                        break;
                    }
                }
            }

            if (!imageFile && clipboardData?.items) {
                for (const item of clipboardData.items) {
                    if (
                        item.kind === 'file' &&
                        item.type.startsWith('image/')
                    ) {
                        imageFile = item.getAsFile();
                        break;
                    }
                }
            }

            if (!imageFile) {
                showStatus(
                    'クリップボードから画像を取得できませんでした。',
                    true
                );
                return;
            }

            setImageFile(imageFile);
        });

        imageInput.addEventListener('change', function() {
            const file = imageInput.files[0];

            if (!file) {
                pasteStatus.hidden = true;
                pasteArea.textContent =
                    'ここをタップまたはクリックして、スクリーンショットを貼り付けてください';
                pasteArea.classList.remove('has-image');
                return;
            }

            if (!file.type.startsWith('image/')) {
                imageInput.value = '';

                showStatus(
                    '画像ファイルを選択してください。',
                    true
                );
                return;
            }

            pasteArea.textContent = '画像を選択済みです';
            pasteArea.classList.add('has-image');

            showStatus(
                '画像を選択しました。登録ボタンを押してください。'
            );
        });
    });
</script>

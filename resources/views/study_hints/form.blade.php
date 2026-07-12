```blade
<label for="subject_id">科目</label>

<select name="subject_id" id="subject_id">
    <option value="">選択してください</option>

    @foreach ($subjects as $subject)
        <option value="{{ $subject->id }}"
            {{ old('subject_id', isset($studyHint) ? $studyHint->book?->subject_id : '') == $subject->id ? 'selected' : '' }}>
            {{ $subject->name }}
        </option>
    @endforeach
</select>
<br><br>

<label for="book_id">参考書</label>

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
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror

<br><br>

<label for="page_number">ページ番号</label>

<input type="number" name="page_number" id="page_number" min="1"
    value="{{ old('page_number', $studyHint->page_number ?? '') }}">

@error('page_number')
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror

<br><br>

<label for="question_no_1">大問番号1</label>

<input type="text" name="question_no_1" id="question_no_1" maxlength="4"
    value="{{ old('question_no_1', $studyHint->question_no_1 ?? '') }}">

@error('question_no_1')
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror

<label for="question_no_2">大問番号2</label>

<input type="text" name="question_no_2" id="question_no_2" maxlength="4"
    value="{{ old('question_no_2', $studyHint->question_no_2 ?? '') }}">

@error('question_no_2')
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror

<label for="question_no_3">大問番号3</label>

<input type="text" name="question_no_3" id="question_no_3" maxlength="4"
    value="{{ old('question_no_3', $studyHint->question_no_3 ?? '') }}">

@error('question_no_3')
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror

@php
    /*
     * 編集画面では、登録済みのproblem_hintsを
     * hint_orderをキーにして取得します。
     */
    $existingHints = isset($studyHint) ? $studyHint->problemHints->keyBy('hint_order') : collect();
@endphp

<h3>ヒント</h3>

<p>
    ヒントは最大3個まで登録できます。<br>
    文章のみ、画像のみ、文章と画像の両方で登録できます。
</p>
<div class="image-color-setting">
    <label>
        <input type="checkbox" name="save_images_in_color" id="save-images-in-color" value="1"
            {{ old('save_images_in_color') ? 'checked' : '' }}>

        画像をカラーで保存する
    </label>

    <p class="image-color-setting-note">
        チェックなしの場合は、選択・貼り付けした画像を
        モノクロに変換してから保存します。
    </p>
</div>

@if ($errors->any())
    <p class="validation-image-notice">
        入力内容に誤りがあったため、選択・貼り付けした画像はリセットされています。
        お手数ですが、画像をもう一度選択または貼り付けしてください。
    </p>
@endif
@for ($index = 0; $index < 3; $index++)
    @php
        $hintNumber = $index + 1;
        $existingHint = $existingHints->get($hintNumber);

        $currentContent = $existingHint?->content;
        $currentImageUrl = $existingHint?->image_url;
    @endphp
    <fieldset class="problem-hint-fieldset">
        <legend>
            ヒント{{ $hintNumber }}
        </legend>

        <label for="hint-content-{{ $index }}">
            ヒント文章
        </label>

        <textarea name="hints[{{ $index }}][content]" id="hint-content-{{ $index }}" rows="5">{{ old("hints.$index.content", $currentContent) }}</textarea>

        @error("hints.$index.content")
            <p style="color:red;">
                {{ $message }}
            </p>
        @enderror

        <br><br>

        @if (filled($currentImageUrl))
            <div class="current-hint-image">
                <p>
                    現在登録されている画像
                </p>

                <img src="{{ $currentImageUrl }}" alt="ヒント{{ $hintNumber }}の現在の画像" class="hint-thumbnail"
                    data-full-image="{{ $currentImageUrl }}">
            </div>

            <input type="hidden" name="hints[{{ $index }}][current_image]" value="{{ $currentImageUrl }}">
        @endif

        <label for="hint-image-{{ $index }}">
            {{ filled($currentImageUrl) ? '新しい画像に差し替える' : 'ヒント画像' }}
        </label>

        <div id="image-paste-area-{{ $index }}" class="image-paste-area" contenteditable="true" role="textbox"
            tabindex="0" aria-label="ヒント{{ $hintNumber }}の画像貼り付け欄">
            ここをタップまたはクリックして、
            スクリーンショットを貼り付けてください
        </div>

        <p id="paste-status-{{ $index }}" class="paste-status" hidden></p>

        <input type="file" name="hints[{{ $index }}][image]" id="hint-image-{{ $index }}"
            accept="image/*">

        @error("hints.$index.image")
            <p style="color:red;">
                {{ $message }}
            </p>
        @enderror

        @if (filled($currentImageUrl))
            <br>

            <label>
                <input type="checkbox" name="hints[{{ $index }}][remove_image]" value="1"
                    {{ old("hints.$index.remove_image") ? 'checked' : '' }}>

                現在の画像を削除する
            </label>
        @endif
    </fieldset>

    <br>
@endfor

@error('hints')
    <p style="color:red;">
        {{ $message }}
    </p>
@enderror
```

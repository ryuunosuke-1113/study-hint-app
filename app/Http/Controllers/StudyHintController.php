<?php

namespace App\Http\Controllers;

use App\Models\StudyHint;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ProblemHint;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudyHintController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::orderBy('name')->get();
        $books = Book::with('subject')->orderBy('name')->get();

        $query = StudyHint::with([
            'book.subject',
            'problemHints',
        ])
            ->join(
                'books',
                'study_hints.book_id',
                '=',
                'books.id'
            )
            ->join(
                'subjects',
                'books.subject_id',
                '=',
                'subjects.id'
            )
            ->select('study_hints.*');
        if ($request->filled('subject_id')) {
            $query->where('subjects.id', $request->subject_id);
        }

        if ($request->filled('book_id')) {
            $query->where('books.id', $request->book_id);
        }

        if ($request->filled('page_number')) {
            $query->where('page_number', $request->page_number);
        }

        if ($request->filled('question_no')) {
            $query->where(function ($q) use ($request) {
                $q->where('question_no_1', $request->question_no)
                    ->orWhere('question_no_2', $request->question_no)
                    ->orWhere('question_no_3', $request->question_no);
            });
        }

        if ($request->filled('hint')) {
            $searchHint = $request->hint;

            $query->where(function ($q) use ($searchHint) {

                /*
                 * 新形式の複数ヒントを検索します。
                 */
                $q->orWhereHas(
                    'problemHints',
                    function ($problemHintQuery) use ($searchHint) {
                        $problemHintQuery->where(
                            'content',
                            'like',
                            '%' . $searchHint . '%'
                        );
                    }
                );
            });
        }
        $studyHints = $query
            ->orderBy('subjects.name')
            ->orderBy('books.name')
            ->orderBy('page_number')
            ->orderBy('question_no_1')
            ->orderBy('question_no_2')
            ->orderBy('question_no_3')
            ->get();

        return view('study_hints.index', compact(
            'studyHints',
            'subjects',
            'books'
        ));
    }
    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        $books = Book::with('subject')->orderBy('name')->get();

        return view('study_hints.create', compact('subjects', 'books'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => [
                'required',
                'exists:books,id',
            ],

            'page_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'question_no_1' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'question_no_2' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'question_no_3' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'hints' => [
                'nullable',
                'array',
                'max:3',
            ],

            'hints.*.content' => [
                'nullable',
                'string',
            ],

            'hints.*.image' => [
                'nullable',
                'image',
                'max:5120',
            ],
        ], [
            'hints.max' =>
                'ヒントは3個まで登録できます。',

            'hints.*.image.image' =>
                '画像ファイルを選択してください。',

            'hints.*.image.max' =>
                '画像は1枚につき5MB以内にしてください。',
        ]);

        $enteredHints = [];

        for ($index = 0; $index < 3; $index++) {
            $content = trim(
                (string) $request->input(
                    "hints.$index.content",
                    ''
                )
            );

            $imageFile = $request->file(
                "hints.$index.image"
            );

            if ($content !== '' || $imageFile) {
                $enteredHints[] = [
                    'index' => $index,
                    'content' => $content !== ''
                        ? $content
                        : null,
                ];
            }
        }

        if (empty($enteredHints)) {
            throw ValidationException::withMessages([
                'hints' =>
                    'ヒント文章または画像を、少なくとも1つ登録してください。',
            ]);
        }

        DB::transaction(function () use ($request, $validated, $enteredHints) {
            $studyHint = StudyHint::create([
                'book_id' => $validated['book_id'],
                'page_number' => $validated['page_number'],

                'question_no_1' =>
                    $validated['question_no_1'] ?? null,

                'question_no_2' =>
                    $validated['question_no_2'] ?? null,

                'question_no_3' =>
                    $validated['question_no_3'] ?? null,

            ]);

            foreach ($enteredHints as $hintData) {
                $index = $hintData['index'];

                $imageFile = $request->file(
                    "hints.$index.image"
                );

                $imageUrl = null;

                if ($imageFile) {
                    $imageUrl =
                        $this->uploadImageToSupabase(
                            $imageFile
                        );
                }

                ProblemHint::create([
                    'study_hint_id' => $studyHint->id,
                    'hint_order' => $index + 1,
                    'content' => $hintData['content'],
                    'image_url' => $imageUrl,
                ]);
            }
        });

        return redirect()
            ->route('study-hints.index')
            ->with(
                'success',
                '問題とヒントを登録しました。'
            );
    }
    public function edit(StudyHint $studyHint)
    {
        $studyHint->load('problemHints');

        $subjects = Subject::orderBy('name')->get();
        $books = Book::with('subject')
            ->orderBy('name')
            ->get();

        return view(
            'study_hints.edit',
            compact(
                'studyHint',
                'subjects',
                'books'
            )
        );
    }
    public function update(Request $request, StudyHint $studyHint)
    {
        $validated = $request->validate([
            'book_id' => [
                'required',
                'exists:books,id',
            ],

            'page_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'question_no_1' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'question_no_2' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'question_no_3' => [
                'nullable',
                'regex:/^(?:[0-9]{1,4}|[A-Za-zァ-ン])$/u',
            ],

            'hints' => [
                'nullable',
                'array',
                'max:3',
            ],

            'hints.*.content' => [
                'nullable',
                'string',
            ],

            'hints.*.image' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'hints.*.current_image' => [
                'nullable',
                'string',
            ],

            'hints.*.remove_image' => [
                'nullable',
                'boolean',
            ],
        ], [
            'hints.max' =>
                'ヒントは3個まで登録できます。',

            'hints.*.image.image' =>
                '画像ファイルを選択してください。',

            'hints.*.image.max' =>
                '画像は1枚につき5MB以内にしてください。',
        ]);

        /*
         * 現在登録されているヒントを
         * hint_orderをキーにして取得します。
         */
        $studyHint->load('problemHints');

        $existingHints = $studyHint->problemHints
            ->keyBy('hint_order');

        /*
         * 更新後にも、文章または画像が
         * 1つ以上残るか確認します。
         */
        $hasAtLeastOneHint = false;

        for ($index = 0; $index < 3; $index++) {
            $hintOrder = $index + 1;
            $existingHint = $existingHints->get($hintOrder);

            $content = trim(
                (string) $request->input(
                    "hints.$index.content",
                    ''
                )
            );

            $newImage = $request->file(
                "hints.$index.image"
            );

            $removeImage = $request->boolean(
                "hints.$index.remove_image"
            );

            $keepsExistingImage =
                $existingHint?->image_url
                && !$removeImage;

            if (
                $content !== ''
                || $newImage
                || $keepsExistingImage
            ) {
                $hasAtLeastOneHint = true;
                break;
            }
        }

        if (!$hasAtLeastOneHint) {
            throw ValidationException::withMessages([
                'hints' =>
                    'ヒント文章または画像を、少なくとも1つ残してください。',
            ]);
        }

        /*
         * 新しくアップロードした画像と、
         * 更新後に削除する古い画像を記録します。
         */
        $uploadedImageUrls = [];
        $imagesToDelete = [];

        try {
            DB::transaction(function () use ($request, $validated, $studyHint, $existingHints, &$uploadedImageUrls, &$imagesToDelete) {
                /*
                 * 問題の基本情報を更新します。
                 */
                $studyHint->update([
                    'book_id' =>
                        $validated['book_id'],

                    'page_number' =>
                        $validated['page_number'],

                    'question_no_1' =>
                        $validated['question_no_1'] ?? null,

                    'question_no_2' =>
                        $validated['question_no_2'] ?? null,

                    'question_no_3' =>
                        $validated['question_no_3'] ?? null,
                ]);

                /*
                 * ヒント1〜3を順番に更新します。
                 */
                for ($index = 0; $index < 3; $index++) {
                    $hintOrder = $index + 1;

                    $existingHint = $existingHints->get(
                        $hintOrder
                    );

                    $content = trim(
                        (string) $request->input(
                            "hints.$index.content",
                            ''
                        )
                    );

                    $newImage = $request->file(
                        "hints.$index.image"
                    );

                    $removeImage = $request->boolean(
                        "hints.$index.remove_image"
                    );

                    $oldImageUrl =
                        $existingHint?->image_url;

                    $finalImageUrl = $oldImageUrl;

                    /*
                     * 新しい画像が選択された場合は、
                     * Supabaseへアップロードします。
                     */
                    if ($newImage) {
                        $finalImageUrl =
                            $this->uploadImageToSupabase(
                                $newImage
                            );

                        $uploadedImageUrls[] =
                            $finalImageUrl;

                        if ($oldImageUrl) {
                            $imagesToDelete[] =
                                $oldImageUrl;
                        }
                    } elseif ($removeImage) {
                        /*
                         * 画像削除が選択された場合。
                         */
                        $finalImageUrl = null;

                        if ($oldImageUrl) {
                            $imagesToDelete[] =
                                $oldImageUrl;
                        }
                    }

                    /*
                     * 文章も画像もなくなった場合は、
                     * ProblemHint自体を削除します。
                     */
                    if (
                        $content === ''
                        && !$finalImageUrl
                    ) {
                        if ($existingHint) {
                            $existingHint->delete();
                        }

                        continue;
                    }

                    $hintValues = [
                        'content' =>
                            $content !== ''
                            ? $content
                            : null,

                        'image_url' =>
                            $finalImageUrl,
                    ];

                    /*
                     * 既存のヒントなら更新、
                     * 未登録の枠なら新規作成します。
                     */
                    if ($existingHint) {
                        $existingHint->update(
                            $hintValues
                        );
                    } else {
                        ProblemHint::create([
                            'study_hint_id' =>
                                $studyHint->id,

                            'hint_order' =>
                                $hintOrder,

                            ...$hintValues,
                        ]);
                    }
                }
            });
        } catch (\Throwable $exception) {
            /*
             * DB更新が失敗した場合、
             * 今回新しくアップロードした画像を
             * Supabaseから削除します。
             */
            foreach ($uploadedImageUrls as $uploadedImageUrl) {
                try {
                    $this->deleteImageFromSupabase(
                        $uploadedImageUrl
                    );
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        /*
         * DB更新が成功した後で、
         * 差し替え前または削除対象の画像を消します。
         */
        $imageDeleteFailed = false;

        foreach (
            array_unique($imagesToDelete)
            as $imageUrl
        ) {
            try {
                $this->deleteImageFromSupabase(
                    $imageUrl
                );
            } catch (\Throwable $exception) {
                report($exception);
                $imageDeleteFailed = true;
            }
        }

        $redirect = redirect()
            ->route('study-hints.index')
            ->with(
                'success',
                '問題とヒントを更新しました。'
            );

        if ($imageDeleteFailed) {
            $redirect->with(
                'warning',
                '更新は完了しましたが、一部の古い画像を削除できませんでした。'
            );
        }

        return $redirect;
    }
    public function destroy(StudyHint $studyHint)
    {
        /*
         * 削除対象のProblemHintと画像を取得します。
         */
        $studyHint->load('problemHints');

        $imageUrls = $studyHint->problemHints
            ->pluck('image_url')
            ->filter()
            ->unique()
            ->values();

        /*
         * 先にDB上のProblemHintとStudyHintを削除します。
         */
        DB::transaction(function () use ($studyHint) {
            $studyHint->problemHints()->delete();
            $studyHint->delete();
        });

        /*
         * Supabase上の画像を削除します。
         */
        $imageDeleteFailed = false;

        foreach ($imageUrls as $imageUrl) {
            try {
                $this->deleteImageFromSupabase(
                    $imageUrl
                );
            } catch (\Throwable $exception) {
                report($exception);
                $imageDeleteFailed = true;
            }
        }

        $redirect = redirect()
            ->route('study-hints.index')
            ->with(
                'success',
                '問題とヒントを削除しました。'
            );

        if ($imageDeleteFailed) {
            $redirect->with(
                'warning',
                '問題は削除しましたが、一部の画像をSupabaseから削除できませんでした。'
            );
        }

        return $redirect;
    }
    private function uploadImageToSupabase($file): string
    {
        $supabaseUrl = rtrim(config('services.supabase.url'), '/');
        $supabaseKey = config('services.supabase.key');
        $bucket = config('services.supabase.bucket');

        if (!$supabaseUrl || !$supabaseKey || !$bucket) {
            throw new \RuntimeException(
                'Supabaseの接続設定が不足しています。'
            );
        }

        $extension = strtolower(
            $file->getClientOriginalExtension() ?: 'jpg'
        );

        $filePath = 'hints/' . Str::uuid() . '.' . $extension;

        $uploadUrl =
            "{$supabaseUrl}/storage/v1/object/{$bucket}/{$filePath}";

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'x-upsert' => 'false',
        ])
            ->withBody(
                file_get_contents($file->getRealPath()),
                $file->getMimeType()
            )
            ->post($uploadUrl);
        if ($response->failed()) {
            throw new \RuntimeException(
                'Supabaseへの画像アップロードに失敗しました。'
                . ' HTTP ' . $response->status()
                . ' ' . $response->body()
            );
        }

        return "{$supabaseUrl}/storage/v1/object/public/"
            . "{$bucket}/{$filePath}";
    }
    private function deleteImageFromSupabase(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        $supabaseUrl = rtrim(config('services.supabase.url'), '/');
        $supabaseKey = config('services.supabase.key');
        $bucket = config('services.supabase.bucket');

        $publicUrlPrefix =
            "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

        // URLから「hints/〇〇.jpg」の部分だけ取り出す
        if (!str_starts_with($imageUrl, $publicUrlPrefix)) {
            return;
        }

        $filePath = substr($imageUrl, strlen($publicUrlPrefix));

        $deleteUrl =
            "{$supabaseUrl}/storage/v1/object/{$bucket}";

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->delete($deleteUrl, [
                    'prefixes' => [$filePath],
                ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                '古い画像の削除に失敗しました。'
                . ' HTTP ' . $response->status()
                . ' ' . $response->body()
            );
        }
    }
}
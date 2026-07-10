<?php

namespace App\Http\Controllers;

use App\Models\StudyHint;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StudyHintController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::orderBy('name')->get();
        $books = Book::with('subject')->orderBy('name')->get();

        $query = StudyHint::with('book.subject')
            ->join('books', 'study_hints.book_id', '=', 'books.id')
            ->join('subjects', 'books.subject_id', '=', 'subjects.id')
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
            $query->where('hint', 'like', '%' . $request->hint . '%');
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
            'book_id' => ['required', 'exists:books,id'],
            'page_number' => ['required', 'integer', 'min:1'],
            'question_no_1' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],
            'question_no_2' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],
            'question_no_3' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],

            'hint' => ['nullable', 'string', 'required_without:images'],
            'images' => ['nullable', 'array', 'max:2', 'required_without:hint'],
            'images.*' => ['image', 'max:5120'],
        ], [
            'hint.required_without' =>
                'ヒント文章または画像のどちらかを入力してください。',
            'images.required_without' =>
                'ヒント文章または画像のどちらかを入力してください。',
            'images.max' =>
                '画像は最大2枚まで登録できます。',
            'images.*.image' =>
                '画像ファイルを選択してください。',
            'images.*.max' =>
                '画像は1枚につき5MB以内にしてください。',
        ]);

        $files = $request->file('images', []);

        if (isset($files[0])) {
            $validated['image_url'] =
                $this->uploadImageToSupabase($files[0]);
        }

        if (isset($files[1])) {
            $validated['image_url_2'] =
                $this->uploadImageToSupabase($files[1]);
        }

        unset($validated['images']);

        StudyHint::create($validated);

        return redirect()->route('study-hints.index')
            ->with('success', 'ヒントを登録しました。');
    }
    public function edit(StudyHint $studyHint)
    {
        $subjects = Subject::orderBy('name')->get();
        $books = Book::with('subject')->orderBy('name')->get();

        return view('study_hints.edit', compact('studyHint', 'subjects', 'books'));
    }
    public function update(Request $request, StudyHint $studyHint)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'page_number' => ['required', 'integer', 'min:1'],
            'question_no_1' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],
            'question_no_2' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],
            'question_no_3' => ['nullable', 'regex:/^[0-9A-Za-zァ-ン]$/u'],
            'hint' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $oldImageUrl = $studyHint->image_url;

            // 先に新しい画像をアップロード
            $validated['image_url'] = $this->uploadImageToSupabase(
                $request->file('image')
            );

            // アップロード成功後、古い画像を削除
            $this->deleteImageFromSupabase($oldImageUrl);
        }

        unset($validated['image']);

        $studyHint->update($validated);

        return redirect()->route('study-hints.index')
            ->with('success', 'ヒントを更新しました。');
    }
    public function destroy(StudyHint $studyHint)
    {
        if ($studyHint->image_url) {
            $this->deleteImageFromSupabase($studyHint->image_url);
        }

        $studyHint->delete();

        return redirect()->route('study-hints.index')
            ->with('success', 'ヒントを削除しました。');
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
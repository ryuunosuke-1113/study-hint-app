<?php

namespace App\Http\Controllers;

use App\Models\StudyHint;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Book;

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
            'hint' => ['required', 'string'],
        ]);
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
        ]);
        $studyHint->update($validated);

        return redirect()->route('study-hints.index')
            ->with('success', 'ヒントを更新しました。');
    }

    public function destroy(StudyHint $studyHint)
    {
        $studyHint->delete();

        return redirect()->route('study-hints.index')
            ->with('success', 'ヒントを削除しました。');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Subject;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('subject')
            ->join('subjects', 'books.subject_id', '=', 'subjects.id')
            ->select('books.*')
            ->orderBy('subjects.name')
            ->orderBy('books.name')
            ->get();

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();

        return view('books.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', '参考書を登録しました。');
    }

    public function edit(Book $book)
    {
        $subjects = Subject::orderBy('name')->get();

        return view('books.edit', compact('book', 'subjects'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $book->update($validated);

        return redirect()->route('books.index')
            ->with('success', '参考書を更新しました。');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '参考書を削除しました。');
    }
}
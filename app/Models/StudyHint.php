<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyHint extends Model
{
    protected $fillable = [
        'book_id',
        'page_number',
        'question_no_1',
        'question_no_2',
        'question_no_3',
        'hint',
        'image_url',
    ];
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
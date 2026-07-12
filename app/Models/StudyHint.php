<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyHint extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'page_number',
        'question_no_1',
        'question_no_2',
        'question_no_3',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function problemHints(): HasMany
    {
        return $this->hasMany(ProblemHint::class)
            ->orderBy('hint_order');
    }
}
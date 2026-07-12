<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemHint extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_hint_id',
        'hint_order',
        'content',
        'image_url',
    ];

    protected $casts = [
        'hint_order' => 'integer',
    ];

    public function studyHint(): BelongsTo
    {
        return $this->belongsTo(StudyHint::class);
    }
}
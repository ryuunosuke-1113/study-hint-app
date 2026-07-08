<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'subject_id',
        'name',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function studyHints()
    {
        return $this->hasMany(StudyHint::class);
    }
}
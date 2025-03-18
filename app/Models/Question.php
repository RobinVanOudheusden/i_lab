<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'question',
        'answer',
        'type',
        'options',
        'correct_option',
        'explanation',
        'image',
        'time'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}

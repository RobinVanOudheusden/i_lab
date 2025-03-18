<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartedQuiz extends Model
{
    protected $fillable = ['quiz_id', 'title', 'description', 'image', 'tags', 'quizcode', 'current_question', 'started', 'show_answers'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    
    public function players()
    {
        return $this->hasMany(Player::class, 'quizcode', 'quizcode');
    }
}   

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quizcode',
        'player_name',
        'question_id',
        'answer',
        'is_correct'
    ];

    /**
     * Get the player that submitted this answer.
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_name', 'name')
            ->where('quizcode', $this->quizcode);
    }

    /**
     * Get the question this answer is for.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
} 
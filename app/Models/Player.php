<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['name', 'quizcode'];
    
    /**
     * Get all answers submitted by this player.
     */
    public function answers()
    {
        return $this->hasMany(PlayerAnswer::class, 'player_name', 'name')
            ->where('quizcode', $this->quizcode);
    }
}

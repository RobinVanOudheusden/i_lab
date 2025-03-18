<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\StartedQuiz;
class JoinController extends Controller
{
    public function joinQuiz($quizcode)
    {
        $name = request('name');
        $quiz_id = StartedQuiz::where('quizcode', $quizcode)->first()->quiz_id;

        // Check if player with same name and code exists
        $existingPlayer = Player::where('name', $name)
            ->where('quizcode', $quizcode)
            ->first();

        if ($existingPlayer) {
            return back()->withErrors([
                'name' => 'Deze naam is al in gebruik voor deze quiz.'
            ]);
        }

        $player = new Player([
            'name' => $name,
            'quizcode' => $quizcode
        ]);
        $player->save();

        return view('game.play', [
            'name' => $name,
            'quizcode' => $quizcode,
            'quiz_id' => $quiz_id
        ]);
    }

    public function leaveQuiz($quizcode)
    {
        $name = request('name');
        
        $player = Player::where('quizcode', $quizcode)
            ->where('name', $name)
            ->first();

        if ($player) {
            $player->delete();
        }

        return redirect('/');
    }

    public function showPlayers($quizcode)
    {
        $players = Player::where('quizcode', $quizcode)->get();
        return response()->json($players);
    }

    public function UrlJoin($quizcode)
    {
        // Redirect to join page with code parameter
        return view('auth.join', [
            'code' => $quizcode
        ]);
    }
}

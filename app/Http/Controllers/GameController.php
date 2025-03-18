<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StartedQuiz;
use App\Models\Quiz;

class GameController extends Controller
{

    public function startQuiz($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $questions = $quiz->questions;
        
        if ($questions->isEmpty()) {
            return redirect()->route('quizzes.host', $id)
                ->with('error', 'Deze quiz heeft geen vragen om te starten.');
        }
        
        // Get the first question to start with
        $firstQuestion = $questions->first();

        // Get the started quiz record and update its status
        $startedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
            
        $startedQuiz->started = true;
        $startedQuiz->ended = false;
        $startedQuiz->show_answers = false;
        $startedQuiz->current_question = $firstQuestion->id;
        $startedQuiz->save();
        
        // return $this->nextQuestion($firstQuestion);
        return $this->showQuestionsScreen($id);
    }

    public function showQuestionsScreen($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        
        // Get the started quiz record to find the current question
        $startedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
            
        if (!$startedQuiz) {
            return redirect()->route('quizzes.host', $id)
                ->with('error', 'Deze quiz is nog niet gestart.');
        }
        
        // Get the current question from the started quiz
        if ($startedQuiz->current_question) {
            $Question = $quiz->questions->firstWhere('id', $startedQuiz->current_question);
        } else {
            // Fallback to the first question if no current question is set
            $Question = $quiz->questions->first();
        }
        
        if (!$Question) {
            return redirect()->route('quizzes.host', $id)
                ->with('error', 'Geen vragen gevonden voor deze quiz.');
        }
        
        return view('game.questionscreen', compact('Question'));
    }

    public function countQuestions(StartedQuiz $startedQuiz)
    {
        $quiz = Quiz::where('title', $startedQuiz->title)
            ->where('description', $startedQuiz->description)
            ->first();
            
        return response()->json([
            'count' => $quiz->questions()->count()
        ]);
    }

    public function getCurrentQuestion(StartedQuiz $startedQuiz) 
    {
        $quiz = Quiz::where('title', $startedQuiz->title)
            ->where('description', $startedQuiz->description)
            ->first();
            
        return response()->json([
            'question' => $quiz->questions()->where('id', $startedQuiz->current_question)->first()
        ]);
    }

    public function getNextQuestion(StartedQuiz $startedQuiz)
    {
        $quiz = Quiz::where('title', $startedQuiz->title)
            ->where('description', $startedQuiz->description)
            ->first();
            
        $nextQuestion = $quiz->questions()
            ->where('id', '>', $startedQuiz->current_question)
            ->orderBy('id')
            ->first();
            
        if ($nextQuestion) {
            $startedQuiz->current_question = $nextQuestion->id;
            $startedQuiz->save();
        }
        
        return response()->json([
            'question' => $nextQuestion
        ]);
    }

    public function currentQuestion($quizcode)
    {
        $startedQuiz = StartedQuiz::where('quizcode', $quizcode)->first();
        
        if (!$startedQuiz) {
            return response()->json([
                'current_question' => null,
                'question' => null
            ]);
        }

        $quiz = Quiz::where('title', $startedQuiz->title)
            ->where('description', $startedQuiz->description)
            ->first();
            
        $question = $quiz->questions()
            ->where('id', $startedQuiz->current_question)
            ->first();

        return response()->json([
            'current_question' => (int) $startedQuiz->current_question,
            'question' => $question ? [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => json_decode($question->options),
                'time' => $question->time,
                'image' => $question->image,
                'correct_option' => $question->correct_option
            ] : null
        ]);
    }

    /**
     * Submit a player's answer to a question
     */
    public function submitAnswer(Request $request, $quizcode)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string',
            'answer' => 'required|string',
            'question_id' => 'required|integer'
        ]);
        
        // Get the question to check if the answer is correct
        $question = \App\Models\Question::find($request->question_id);
        
        // Determine if the answer is correct
        $isCorrect = false;
        if ($question) {
            // Compare the submitted answer with the correct option
            // Convert both to strings to ensure proper comparison
            $isCorrect = (string)$request->answer === (string)$question->correct_option;
        }
        
        // Store the answer in the database
        $playerAnswer = new \App\Models\PlayerAnswer([
            'quizcode' => $quizcode,
            'player_name' => $request->name,
            'question_id' => $request->question_id,
            'answer' => $request->answer,
            'is_correct' => $isCorrect
        ]);
        
        $playerAnswer->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Answer submitted successfully'
        ]);
    }

    /**
     * Get all answers for a specific question
     */
    public function getQuestionAnswers($quiz_id, $question_id)
    {
        // Find the quiz
        $quiz = Quiz::findOrFail($quiz_id);
        
        // Find the started quiz
        $startedQuiz = StartedQuiz::where('quiz_id', $quiz_id)->first();
        
        if (!$startedQuiz) {
            return response()->json([]);
        }
        
        // Get the quizcode
        $quizcode = $startedQuiz->quizcode;
        
        // Get all answers for this question
        $answers = \App\Models\PlayerAnswer::where('quizcode', $quizcode)
            ->where('question_id', $question_id)
            ->get();
        
        return response()->json($answers);
    }

    /**
     * Advance to the next question in the quiz
     */
    public function nextQuestion(Request $request, $quiz_id)
    {
        $quiz = Quiz::with('questions')->findOrFail($quiz_id);
        
        // Find the started quiz
        $startedQuiz = StartedQuiz::where('quiz_id', $quiz_id)->first();
        
        if (!$startedQuiz) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz not started'
            ]);
        }
        
        // Get the current question
        $currentQuestionId = $startedQuiz->current_question;
        
        // Find the next question
        $nextQuestion = $quiz->questions()
            ->where('id', '>', $currentQuestionId)
            ->orderBy('id')
            ->first();
        
        if (!$nextQuestion) {
            // If no next question, the quiz is finished!
            $startedQuiz->ended = true;
            $startedQuiz->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Quiz completed',
                'is_completed' => true
            ]);
        }
        
        // Update the started quiz with the new question
        $startedQuiz->current_question = $nextQuestion->id;
        $startedQuiz->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Advanced to next question',
            'current_question' => $nextQuestion->id
        ]);
    }

    /**
     * Show the leaderboard at the end of the quiz
     */
    public function showLeaderboard($quiz_id)
    {
        $quiz = Quiz::findOrFail($quiz_id);
        $startedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
            
        if (!$startedQuiz) {
            return redirect()->route('quizzes.host', $quiz_id)
                ->with('error', 'Deze quiz is nog niet gestart.');
        }
        
        // Get all players and their score
        $players = $startedQuiz->players()->get();
        
        // Calculate the score for each player
        $playerScores = [];
        foreach ($players as $player) {
            $correctAnswers = $player->answers()
                ->where('is_correct', true)
                ->count();
                
            $playerScores[] = [
                'name' => $player->name,
                'score' => $correctAnswers * 500, // 500 points per correct answer
                'correct_answers' => $correctAnswers
            ];
        }
        
        // Sort players by score (highest first)
        usort($playerScores, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return view('game.leaderboard', [
            'quiz' => $quiz,
            'players' => $playerScores
        ]);
    }
    
    /**
     * Get the current status of a quiz (started, ended, etc.)
     */
    public function getQuizStatus($quizcode)
    {
        // Log the request
        \Log::info('Getting quiz status for quizcode: ' . $quizcode);
        
        // Find the started quiz by quizcode directly, not by quiz ID
        $startedQuiz = StartedQuiz::where('quizcode', $quizcode)->first();
        
        if (!$startedQuiz) {
            \Log::info('No started quiz found for quizcode: ' . $quizcode);
            return response()->json([
                'started' => false,
                'ended' => false,
                'has_current_question' => false
            ]);
        }
        
        // Check if there's a current question set
        $hasCurrentQuestion = $startedQuiz->current_question ? true : false;
        
        // Log the status values
        \Log::info('Quiz status for quizcode ' . $quizcode . ': started=' . 
            ($startedQuiz->started ? 'true' : 'false') . ', ended=' . 
            ($startedQuiz->ended ? 'true' : 'false') . 
            ', has_current_question=' . ($hasCurrentQuestion ? 'true' : 'false'));
            
        return response()->json([
            'started' => (bool)$startedQuiz->started,
            'ended' => (bool)$startedQuiz->ended,
            'has_current_question' => $hasCurrentQuestion
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\StartedQuiz;
use App\Models\Player;

class HostController extends Controller
{
    public function hostQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        // Check if there's already a started quiz with this quiz's data
        $existingStartedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
        
        if ($existingStartedQuiz) {
            // Use the existing quiz code
            $quizcode = $existingStartedQuiz->quizcode;
            
            // Reset quiz state
            $existingStartedQuiz->show_answers = false;
            $existingStartedQuiz->started = false;
            $existingStartedQuiz->ended = false; // Make sure to reset the ended state too
            $existingStartedQuiz->current_question = 0; // Reset current question to 0
            $existingStartedQuiz->save();
            
            // Delete all player answers for this quiz
            \App\Models\PlayerAnswer::where('quizcode', $quizcode)->delete();
            
            // Delete all players with this quiz code
            \App\Models\Player::where('quizcode', $quizcode)->delete();
        } else {
            // Generate a unique 5 digit code for the quiz
            $quizcode = mt_rand(10000, 99999);
            
            // Create a new record in startedquizzes table with quiz data
            $startedQuiz = StartedQuiz::create([
                'quiz_id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'image' => $quiz->image,
                'tags' => $quiz->tags,
                'quizcode' => $quizcode,
                'show_answers' => false,
                'started' => false,
                'ended' => false,
                'current_question' => 0 // Initialize current question to 0
            ]);
        }
        
        return view('quizzes.host', compact('quiz', 'quizcode'));
    }

    

    public function checkShowAnswers($quizcode)
    {
        dd();
    }

    
    public function showAnswers($id)
    {

        $quiz = Quiz::with('questions')->findOrFail($id);
        
        // Get the started quiz record and update its status
        $startedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
            
        $startedQuiz->show_answers = true;
        $startedQuiz->save();
        
        return response()->json(['status' => 'success']);
    }

    private function nextQuestion($question)
    {   
        // Determine the question type and return the appropriate view
        $questionType = $question->type;
        // Get total questions count and current question number
        $totalQuestions = $question->quiz->questions->count();
        $questionNumber = $question->quiz->questions->search($question) + 1;
        $time_limit = $question->time;
        
        // Add these variables to be passed to the view
        $viewData = compact('question', 'totalQuestions', 'questionNumber', 'time_limit');
        
        if ($questionType === 'multiplechoice') {
            return view('questions.multiplechoice', $viewData);
        } else if ($questionType === 'truefalse') {
            return view('questions.truefalse', $viewData);
        } else {
            // Fallback for any other question type
            return redirect()->back()->with('error', 'Onbekend vraagtype.');
        }
    }
    public function joinQuiz($quizcode)
    {
        return view('quizzes.join', compact('id', 'quizcode'));
    }
    public function checkQuizCode($quizcode)
    {
        $startedQuiz = StartedQuiz::where('quizcode', $quizcode)->first();
        if ($startedQuiz) {
            return response()->json([
                'valid' => true,
                'title' => $startedQuiz->title
            ]);
        }
        return response()->json(['valid' => false]);
    }

    public function checkIfStarted($id)
    {
        \Log::info('HostController checkIfStarted called with ID: ' . $id);
        
        $quiz = Quiz::findOrFail($id);
        $startedQuiz = StartedQuiz::where('title', $quiz->title)
            ->where('description', $quiz->description)
            ->first();
        
        if ($startedQuiz) {
            \Log::info('Found started quiz, status: started=' . 
                ($startedQuiz->started ? 'true' : 'false') . ', ended=' . 
                ($startedQuiz->ended ? 'true' : 'false'));
                
            return response()->json([
                'started' => (bool)$startedQuiz->started,
                'ended' => (bool)$startedQuiz->ended
            ]);
        }
        
        \Log::info('No started quiz found for ID: ' . $id);
        return response()->json([
            'started' => false,
            'ended' => false
        ]);
    }

    
}

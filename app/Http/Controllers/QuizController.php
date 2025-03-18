<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\StartedQuiz;

class QuizController extends Controller
{
    public function index()
    {
        // Check if logged in user is a student
        if (auth()->user()->role === 'student') {
            return redirect()->route('auth.join');
        }

        // Check if user is admin or teacher
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'teacher') {
            return redirect()->route('auth.join');
        }

        // Check if table exists before querying
        if (!\Schema::hasTable('quizzes')) {
            // Return empty collection if table doesn't exist yet
            $quizzes = collect([]);
        } else {
            $quizzes = Quiz::all();
            $questions = Question::all();
        }
        
        return view('quizzes.index', compact('quizzes', 'questions')); 
    }

    public function create()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }
        return view('quizzes.create');
    }

    public function store(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('quizimages'), $filename);
            $data['image'] = '/quizimages/' . $filename;
        } else {
            $data['image'] = '/default.jpg';
        }

        // Set empty string for tags if not provided
        if (!isset($data['tags'])) {
            $data['tags'] = '';
        }
        $quiz = Quiz::create($data);
        return redirect()->route('quizzes.index');
    }

    public function edit($id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id);
        return view('quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id);
        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('quizimages'), $filename);
            $data['image'] = '/quizimages/' . $filename;
        }
        
        // Ensure tags is not null
        if (!isset($data['tags'])) {
            $data['tags'] = '';
        }
        
        $quiz->update($data);
        return redirect()->route('quizzes.index');
    }

    public function destroy($id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id);
        
        // Delete all related started quizzes first
        StartedQuiz::where('quiz_id', $id)->delete();
        
        // Delete all related questions
        $quiz->questions()->delete();
        
        // Finally delete the quiz
        $quiz->delete();
        
        return redirect()->route('quizzes.index');
    }
    
    public function show($id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id);
        return view('quizzes.show', compact('quiz'));
    }


    public function showQuestions($id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id);
        $questions = $quiz->questions;
        
        if (!$quiz) {
            return redirect()->route('quizzes.index')->with('error', 'Quiz not found');
        }
        return view('quizzes.questions.index', compact('quiz', 'questions'));
    }

    public function createQuestion($id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id);
        return view('quizzes.questions.create', compact('quiz'));
    }

    public function storeQuestion(Request $request, $id)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id);
        $data = $request->all();
        $data['quiz_id'] = $id;
        
        // Set the answer field based on question type to fix NOT NULL constraint
        if ($request->type === 'multiplechoice') {
            $data['answer'] = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            $data['answer'] = $request->tf_correct_option;
        } else {
            $data['answer'] = ''; // Fallback default value
        }
        
        $question = new Question($data);
        // Process options and correct_option based on question type
        if ($request->type === 'multiplechoice') {
            // For multiple choice, store options as JSON array
            $options = array_filter(explode("\n", $request->options));
            $question->options = json_encode($options);
            $question->correct_option = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            // For true/false, set correct_option from tf_correct_option
            $question->options = json_encode(['true', 'false']);
            $question->correct_option = $request->tf_correct_option;
        }
        
        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('questionimages'), $filename);
            $question->image = '/questionimages/' . $filename;
        }
        
        $question->save();
        return redirect()->route('quizzes.questions.index', $id);
    }

    public function destroyQuestion($quiz, $question)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($question);
        // Delete any associated files/images if needed
        if ($question->image && $question->image != '/default.jpg') {
            $imagePath = public_path(ltrim($question->image, '/'));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $question->delete();
        return redirect()->route('quizzes.questions.index', $quiz);
    }

    public function editQuestion($id, $questionId)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($questionId);
        return view('quizzes.questions.edit', compact('question'));
    }

    public function updateQuestion(Request $request, $id, $questionId)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($questionId);
        
        $data = $request->all();
        
        // Process options and correct_option based on question type
        if ($request->type === 'multiplechoice') {
            // For multiple choice, store options as JSON array
            $options = array_filter(explode("\n", $request->options));
            $data['options'] = json_encode($options);
            $data['correct_option'] = $request->correct_option;
            $data['answer'] = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            // For true/false, set correct_option from tf_correct_option
            $data['options'] = json_encode(['true', 'false']);
            $data['correct_option'] = $request->tf_correct_option;
            $data['answer'] = $request->tf_correct_option;
        }
        
        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('questionimages'), $filename);
            $data['image'] = '/questionimages/' . $filename;
        }
        
        $question->update($data);
        return redirect()->route('quizzes.questions.index', $id);
    }

}

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminController;
Route::get('/', function () {
    return view('auth.join');
})->name('auth.join');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create'); // Moved this route before the {quiz} route
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::put('/admin/update-role', [AdminController::class, 'updateRole'])->name('admin.update-role');
    Route::get('/quizzes/join', function () {
    return view('auth.join');
});
});



Route::post('/quizzes/join', function () {
    return view('auth.join');
})->name('quizzes.join.submit');

Route::get('/quizzes/host', [HostController::class, 'index'])->name('quizzes.host');

Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');

Route::get('/quizzes/{quiz}/host', [HostController::class, 'hostQuiz'])->name('quizzes.host')->where('quiz', '[0-9]+');

Route::post('/quizzes/{quiz}/start', [GameController::class, 'startQuiz'])->name('quizzes.start')->where('quiz', '[0-9]+');

Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');

Route::put('/quizzes/{quiz}/update', [QuizController::class, 'update'])->name('quizzes.update');

Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.delete');

Route::get('/quizzes/{quiz}/questions', [QuizController::class, 'showQuestions'])->name('quizzes.questions.index');

Route::get('/quizzes/{quiz}/questions/create', [QuizController::class, 'createQuestion'])->name('quizzes.questions.create');

Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');

Route::get('/quizzes/{quiz}/questions/{question}/edit', [QuizController::class, 'editQuestion'])->name('quizzes.questions.edit')->where(['quiz' => '[0-9]+', 'question' => '[0-9]+']);

Route::post('/quizzes/{quiz}/questions/{question}/update', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update')->where(['quiz' => '[0-9]+', 'question' => '[0-9]+']);

Route::delete('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy')->where(['quiz' => '[0-9]+', 'question' => '[0-9]+']);

Route::post('/quizzes/check-code/{code}', [HostController::class, 'checkQuizCode'])->name('quizzes.check-code');

Route::post('/play/{quizcode}', [JoinController::class, 'joinQuiz'])->name('quizzes.join');

Route::get('/join/{quizcode}', [JoinController::class, 'UrlJoin'])->name('url.join');

Route::post('/leave/{quizcode}', [JoinController::class, 'leaveQuiz'])->name('leave.quiz');

Route::post('/quizzes/{quizcode}/players', [JoinController::class, 'showPlayers'])->name('quizzes.players.show');

Route::get('/show-answers/{quizcode}', [HostController::class, 'checkShowAnswers'])->name('quizzes.show-answers');

Route::post('/quizzes/{quizcode}/show-answers', [HostController::class, 'showAnswers'])->name('quizzes.show-answers');

Route::post('/quizzes/{quizcode}/checkifstarted', [HostController::class, 'checkIfStarted'])->name('quizzes.checkifstarted');

Route::post('/quizzes/{quizcode}/current', [GameController::class, 'currentQuestion'])->name('quizzes.current');

// New route to submit and track player answers
Route::post('/quizzes/{quizcode}/answer', [GameController::class, 'submitAnswer'])->name('quizzes.submit-answer');

// New route to get all answers for a specific question (for the host)
Route::get('/quizzes/{quiz_id}/answers/{question_id}', [GameController::class, 'getQuestionAnswers'])->name('quizzes.question-answers');

// New route to advance to the next question
Route::post('/quizzes/{quiz_id}/next-question', [GameController::class, 'nextQuestion'])->name('quizzes.next-question');

// New GET route to show the question screen (avoiding the POST method issue)
Route::get('/quizzes/{quiz_id}/question-screen', [GameController::class, 'showQuestionsScreen'])->name('quizzes.question-screen');

// New route to show the leaderboard (end of quiz)
Route::get('/quizzes/{quiz_id}/leaderboard', [GameController::class, 'showLeaderboard'])->name('quizzes.leaderboard');

// New route to check quiz status for players
Route::post('/quizzes/{quizcode}/status', [GameController::class, 'getQuizStatus'])->name('quizzes.status');

// Temporary debug route
Route::get('/debug/quizzes', function() {
    $startedQuizzes = \App\Models\StartedQuiz::all();
    return response()->json($startedQuizzes);
});

// Temporary route to reset a quiz's ended status
Route::get('/debug/reset-quiz/{quizcode}', function($quizcode) {
    $startedQuiz = \App\Models\StartedQuiz::where('quizcode', $quizcode)->first();
    if ($startedQuiz) {
        $startedQuiz->ended = false;
        $startedQuiz->save();
        return response()->json([
            'message' => 'Quiz reset successfully',
            'quiz' => $startedQuiz
        ]);
    }
    return response()->json(['message' => 'Quiz not found'], 404);
});

require __DIR__.'/auth.php';

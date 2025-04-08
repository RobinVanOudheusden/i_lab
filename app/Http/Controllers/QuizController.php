<?php

namespace App\Http\Controllers; // Geeft aan dat deze controller zich in de App\Http\Controllers namespace bevindt

use Illuminate\Http\Request; // Importeert de Request klasse voor het ophalen van data uit aanvragen
use App\Models\Quiz; // Importeert het Quiz model
use App\Models\Question; // Importeert het Question model
use App\Models\StartedQuiz; // Importeert het StartedQuiz model

class QuizController extends Controller // Definieert de QuizController klasse die de basis Controller uitbreidt
{
    public function index()
    {
        // Als de ingelogde gebruiker student is, doorverwijzen naar de join-pagina
        if (auth()->user()->role === 'student') {
            return redirect()->route('auth.join');
        }

        // Als gebruiker geen admin of teacher is, ook doorverwijzen naar join-pagina
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'teacher') {
            return redirect()->route('auth.join');
        }

        // Controleer of de 'quizzes' tabel bestaat
        if (!\Schema::hasTable('quizzes')) {
            $quizzes = collect([]); // Als de tabel niet bestaat, een lege collectie gebruiken
        } else {
            $quizzes = Quiz::all(); // Alle quizzen ophalen
            $questions = Question::all(); // Alle vragen ophalen
        }
        
        // De view 'quizzes.index' tonen met de variabelen quizzes en questions
        return view('quizzes.index', compact('quizzes', 'questions')); 
    }

    public function create()
    {
        // Alleen admin mag quizzen aanmaken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        return view('quizzes.create'); // Laadt het formulier voor het aanmaken van een quiz
    }

    public function store(Request $request)
    {
        // Alleen admin mag quizzen opslaan
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $data = $request->all(); // Alle invoergegevens ophalen
        
        // Als er een afbeelding is geüpload, verwerk die
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName(); // Unieke naam maken
            $image->move(public_path('quizimages'), $filename); // Verplaatsen naar public folder
            $data['image'] = '/quizimages/' . $filename; // Opslaan van pad in database
        } else {
            $data['image'] = '/default.jpg'; // Standaardafbeelding gebruiken als er geen geüpload is
        }

        // Als er geen tags zijn opgegeven, zet ze op een lege string
        if (!isset($data['tags'])) {
            $data['tags'] = '';
        }

        $quiz = Quiz::create($data); // Maak een nieuwe quiz aan
        return redirect()->route('quizzes.index'); // Ga terug naar de quiz-overzichtspagina
    }

    public function edit($id)
    {
        // Alleen admin mag een quiz bewerken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id); // Zoek de quiz op basis van het ID
        return view('quizzes.edit', compact('quiz')); // Toon de bewerkpagina
    }

    public function update(Request $request, $id)
    {
        // Alleen admin mag een quiz bijwerken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id); // Zoek de quiz op
        $data = $request->all(); // Haal alle nieuwe data op

        // Als er een afbeelding is, verwerk deze
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('quizimages'), $filename);
            $data['image'] = '/quizimages/' . $filename;
        }

        // Als tags ontbreken, zet die dan op een lege string
        if (!isset($data['tags'])) {
            $data['tags'] = '';
        }

        $quiz->update($data); // Update de quiz
        return redirect()->route('quizzes.index');
    }

    public function destroy($id)
    {
        // Alleen admin mag quizzen verwijderen
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::find($id); // Zoek de quiz op

        // Verwijder eerst alle gestarte quizzen die hieraan gekoppeld zijn
        StartedQuiz::where('quiz_id', $id)->delete();

        // Verwijder ook alle vragen van deze quiz
        $quiz->questions()->delete();

        // Verwijder dan de quiz zelf
        $quiz->delete();

        return redirect()->route('quizzes.index');
    }

    public function show($id)
    {
        // Alleen admin mag een quiz bekijken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id); // Zoek de quiz of faal als die er niet is
        return view('quizzes.show', compact('quiz'));
    }

    public function showQuestions($id)
    {
        // Alleen admin mag vragen van een quiz bekijken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id); // Zoek de quiz of faal
        $questions = $quiz->questions; // Haal de bijbehorende vragen op

        if (!$quiz) {
            return redirect()->route('quizzes.index')->with('error', 'Quiz not found');
        }

        return view('quizzes.questions.index', compact('quiz', 'questions')); // Toon de vragenlijst
    }

    public function createQuestion($id)
    {
        // Alleen admin mag vragen toevoegen
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id); // Zoek de quiz
        return view('quizzes.questions.create', compact('quiz')); // Laadt de vraag-aanmaakpagina
    }

    public function storeQuestion(Request $request, $id)
    {
        // Alleen admin mag vragen opslaan
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $quiz = Quiz::findOrFail($id); // Zoek de quiz
        $data = $request->all();
        $data['quiz_id'] = $id; // Voeg quiz ID toe aan de data

        // Zorg ervoor dat er altijd een antwoord is (om database fouten te voorkomen)
        if ($request->type === 'multiplechoice') {
            $data['answer'] = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            $data['answer'] = $request->tf_correct_option;
        } else {
            $data['answer'] = '';
        }

        $question = new Question($data); // Maak een nieuwe vraag aan

        // Verwerk de opties voor multiple choice
        if ($request->type === 'multiplechoice') {
            $options = array_filter(explode("\n", $request->options)); // Splits regels
            $question->options = json_encode($options); // Zet opties om in JSON
            $question->correct_option = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            $question->options = json_encode(['true', 'false']); // Voor true/false vaste opties
            $question->correct_option = $request->tf_correct_option;
        }

        // Verwerk afbeelding als die is geüpload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('questionimages'), $filename);
            $question->image = '/questionimages/' . $filename;
        }

        $question->save(); // Sla de vraag op
        return redirect()->route('quizzes.questions.index', $id);
    }

    public function destroyQuestion($quiz, $question)
    {
        // Alleen admin mag vragen verwijderen
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($question); // Zoek de vraag

        // Verwijder afbeelding als die bestaat en niet de default is
        if ($question->image && $question->image != '/default.jpg') {
            $imagePath = public_path(ltrim($question->image, '/'));
            if (file_exists($imagePath)) {
                unlink($imagePath); // Verwijder bestand
            }
        }

        $question->delete(); // Verwijder de vraag
        return redirect()->route('quizzes.questions.index', $quiz);
    }

    public function editQuestion($id, $questionId)
    {
        // Alleen admin mag vragen bewerken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($questionId); // Zoek de vraag
        return view('quizzes.questions.edit', compact('question')); // Toon bewerkpagina
    }

    public function updateQuestion(Request $request, $id, $questionId)
    {
        // Alleen admin mag vragen bijwerken
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }

        $question = Question::findOrFail($questionId); // Zoek de vraag
        $data = $request->all(); // Haal data op

        // Verwerk opties en correcte antwoord
        if ($request->type === 'multiplechoice') {
            $options = array_filter(explode("\n", $request->options));
            $data['options'] = json_encode($options);
            $data['correct_option'] = $request->correct_option;
            $data['answer'] = $request->correct_option;
        } else if ($request->type === 'truefalse') {
            $data['options'] = json_encode(['true', 'false']);
            $data['correct_option'] = $request->tf_correct_option;
            $data['answer'] = $request->tf_correct_option;
        }

        // Verwerk afbeelding als geüpload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('questionimages'), $filename);
            $data['image'] = '/questionimages/' . $filename;
        }

        $question->update($data); // Werk de vraag bij
        return redirect()->route('quizzes.questions.index', $id);
    }
}
// Dit is de QuizController die verantwoordelijk is voor het beheren van quizzen en vragen.
// De controller bevat methoden voor het weergeven, aanmaken, bewerken en verwijderen van quizzen en vragen.
// De controller controleert ook de rol van de ingelogde gebruiker om ervoor te zorgen dat alleen admins toegang hebben tot bepaalde functies.
// De controller maakt gebruik van de Quiz, Question en StartedQuiz modellen om gegevens te beheren.
// De controller maakt ook gebruik van de Request klasse om gegevens van formulieren te verwerken.
// De controller bevat methoden voor het weergeven van quizzen, het aanmaken van nieuwe quizzen, het bewerken en verwijderen van quizzen,
// en het beheren van vragen binnen quizzen.            
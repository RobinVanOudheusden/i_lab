@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-7xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
      <div class="flex justify-start mb-2">
          <a href="{{ route('quizzes.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i>
          </a>
        </div>
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Vragen voor quiz: {{ $quiz->title }}</h2>
        <div class="overflow-x-auto">
          <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 shadow-xl border-2 border-info p-4">
            @if (!isset($questions) || $questions === null || ($questions) === 0)
              <p class="text-center text-lg italic opacity-50">Geen vragen gevonden voor deze quiz...</p>
              <p class="text-center text-md">Voeg <a href="{{ route('quizzes.questions.create', $quiz->id) }}" class="link link-hover text-secondary">hier</a> een nieuwe vraag toe!</p>
            @else
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($questions as $question)
                <div class="card bg-base-200 shadow-lg">
                  @if($question->image)
                  <figure class="px-4 pt-4">
                    <img src="{{ $question->image }}" alt="Question Image" class="rounded-xl w-128" />
                  </figure>
                  @endif
                  <div class="card-body">
                    <h3 class="card-title text-lg question-title">{{ $question->question }}</h3>
                    <p class="question-type">Type: <span class="badge badge-primary">{{ ucfirst($question->type) }}</span></p>
                    
                    <div class="mt-2">
                      <p class="font-semibold">Antwoord opties:</p>
                      @if($question->type == 'multiplechoice')
                        @php
                          $options = json_decode($question->options, true);
                        @endphp
                        <ul class="list-disc list-inside">
                          @foreach($options ?? [] as $option)
                            <li class="{{ $option == $question->correct_option ? 'text-success font-bold' : '' }}">{{ $option }}</li>
                          @endforeach
                        </ul>
                      @elseif($question->type == 'truefalse')
                        <p>Correct antwoord: <span class="font-bold">{{ $question->correct_option == 'true' ? 'Waar' : 'Niet waar' }}</span></p>
                      @else
                        <p>Correct antwoord: <span class="font-bold">{{ $question->correct_option }}</span></p>
                      @endif
                    </div>
                    
                    @if($question->explanation)
                    <div class="mt-2">
                      <p class="font-semibold">Uitleg:</p>
                      <p class="italic">{{ $question->explanation }}</p>
                    </div>
                    @endif
                    
                    <div class="divider my-2"></div>
                    <div class="card-actions justify-end mt-4">
                      <div class="flex justify-between items-center w-full">
                        <span class="font-medium">Acties:</span>
                        <div>
                          <div class="tooltip" data-tip="Vraag bewerken">
                            <a href="{{ route('quizzes.questions.edit', ['quiz' => $question->quiz_id, 'question' => $question->id]) }}" class="btn btn-ghost">
                              <i class="fa-solid fa-pencil text-warning text-xl"></i>
                            </a>
                          </div>
                          <div class="tooltip" data-tip="Vraag verwijderen">
                            <button onclick="openDeleteModal('{{ $quiz->id }}', '{{ $question->id }}')" class="btn btn-ghost">
                              <i class="fa-solid fa-xmark text-error text-xl"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              
              <div class="divider"></div>
              <div class="flex justify-between mb-4">
                <a href="{{ route('quizzes.index') }}" class="btn btn-error">
                  <i class="fas fa-arrow-left mr-2"></i>
                  Terug naar quizzen
                </a>
                <a href="{{ route('quizzes.questions.create', $quiz->id) }}" class="btn btn-info">
                  <i class="fas fa-plus mr-2"></i>
                  Vraag toevoegen
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
  <div class="modal-box border-2 border-error">
    <h3 class="font-bold text-lg">Vraag verwijderen</h3>
    <p class="py-4">Weet je zeker dat je deze vraag wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>
    <div class="modal-action">
      <button class="btn" onclick="document.getElementById('delete_confirm_modal').close()">Annuleren</button>
      <form id="delete-question-form" action="" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-error">Verwijderen</button>
      </form>
    </div>
  </div>
</dialog>

@include('layouts.footer')

<script>
function openDeleteModal(quizId, questionId) {
    const form = document.getElementById('delete-question-form');
    form.action = `/quizzes/${quizId}/questions/${questionId}`;
    document.getElementById('delete_confirm_modal').showModal();
}
</script>

@endsection
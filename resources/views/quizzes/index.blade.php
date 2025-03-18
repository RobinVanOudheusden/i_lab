@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-7xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Alle quizzen:</h2>
        
        <div class="mb-4">
          <div class="form-control">
            <div class="input-group relative">
              <input type="text" id="quizSearch" placeholder="Zoeken..." class="input input-bordered w-full" />
              <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="clearQuizSearch()">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 shadow-xl border-2 border-info p-4">
            @if ($quizzes->isEmpty())
              <p class="text-center text-lg italic opacity-50">Geen quizzen gevonden...</p>
              @if(auth()->user()->role === 'admin')
                <p class="text-center text-md">Maak <a href="{{ route('quizzes.create') }}" class="link link-hover text-secondary">hier</a> een nieuwe quiz aan!</p>
              @endif
            @else
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="quizGrid">
                @foreach ($quizzes as $quiz)
                <div class="card bg-base-200 shadow-lg quiz-card" data-title="{{ strtolower($quiz->title) }}" data-description="{{ strtolower($quiz->description) }}" data-tags="{{ strtolower($quiz->tags) }}">
                  <figure class="px-4 pt-4">
                    <img src="{{ $quiz->image }}" alt="Quiz Image" class="rounded-xl w-128" />
                  </figure>
                  <div class="card-body">
                    <h3 class="card-title text-lg quiz-title">{{ $quiz->title }}</h3>
                    <p class="quiz-description">{{ $quiz->description }}</p>
                    
                    @php
                      $tags = array_filter(array_map('trim', explode(',', $quiz->tags)));
                      $questionCount = $quiz->questions->count();
                    @endphp
                    <div class="mt-2 quiz-tags" data-tags="{{ implode(',', $tags) }}">
                      @if(count($tags) <= 3)
                        @foreach($tags as $tag)
                          <div class="badge badge-info mr-1">{{ $tag }}</div>
                        @endforeach
                      @else
                        @foreach(array_slice($tags, 0, 2) as $tag)
                          <div class="badge badge-info mr-1">{{ $tag }}</div>
                        @endforeach
                        <div class="dropdown dropdown-hover inline-block">
                          <div tabindex="0" class="badge badge-info cursor-pointer">+{{ count($tags) - 2 }} meer</div>
                          <div tabindex="0" class="dropdown-content z-[1] card card-compact w-64 p-2 shadow bg-base-100">
                            <div class="card-body flex flex-wrap gap-2">
                              @foreach(array_slice($tags, 2) as $tag)
                                <div class="badge badge-info">{{ $tag }}</div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      @endif
                    </div>
                    <div class="badge badge-error mt-2">{{ $questionCount }} {{ $questionCount === 1 ? 'vraag' : 'vragen' }}</div>
                    <div class="divider my-2"></div>
                    <div class="card-actions justify-end mt-4">
                      <div class="flex justify-between items-center w-full">
                        <span class="font-medium">Acties:</span>
                        <div>
                          <div class="tooltip" data-tip="Quiz hosten">
                            <button class="btn btn-ghost" onclick="window.location.href='{{ route('quizzes.host', $quiz->id) }}'">
                              <i class="fa-solid fa-play text-success text-xl"></i>
                            </button>
                          </div>
                          @if(auth()->user()->role === 'admin')
                          <div class="tooltip" data-tip="Quiz bewerken">
                            <button class="btn btn-ghost" onclick="window.location.href='{{ route('quizzes.edit', $quiz->id) }}'">
                              <i class="fa-solid fa-pencil text-warning text-xl"></i>
                            </button>
                          </div>
                          <div class="tooltip" data-tip="Quiz verwijderen">
                            <button class="btn btn-ghost" onclick="openDeleteModal({{ $quiz->id }})">
                              <i class="fa-solid fa-xmark text-error text-xl"></i>
                            </button>
                          </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              
              
              @if(auth()->user()->role === 'admin')
              <div class="divider"></div>
                <div class="flex justify-end mb-4">
                  <a href="{{ route('quizzes.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-2"></i>
                    Quiz toevoegen
                  </a>
                </div>
              @endif
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
    <h3 class="font-bold text-lg">Quiz verwijderen</h3>
    <p class="py-4">Weet je zeker dat je deze quiz wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>
    <div class="modal-action">
      <button class="btn" onclick="document.getElementById('delete_confirm_modal').close()">Annuleren</button>
      <form id="delete-quiz-form" action="" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-error">Verwijderen</button>
      </form>
    </div>
  </div>
</dialog>

@include('layouts.footer')

<script>
  function openDeleteModal(quizId) {
    const modal = document.getElementById('delete_confirm_modal');
    const deleteForm = document.getElementById('delete-quiz-form');
    if (deleteForm) {
      deleteForm.action = `/quizzes/${quizId}`;
      modal.showModal();
    } else {
      console.error("Delete form element not found");
    }
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('quizSearch');
    const quizCards = document.querySelectorAll('.quiz-card');
    
    searchInput.addEventListener('keyup', filterQuizzes);
  });
  
  function filterQuizzes() {
    const searchInput = document.getElementById('quizSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const quizCards = document.querySelectorAll('.quiz-card');
    
    quizCards.forEach(card => {
      const title = card.getAttribute('data-title');
      const description = card.getAttribute('data-description');
      const tags = card.getAttribute('data-tags');
      
      if (title.includes(searchTerm) || description.includes(searchTerm) || tags.includes(searchTerm)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  }
  
  function clearQuizSearch() {
    const searchInput = document.getElementById('quizSearch');
    searchInput.value = '';
    
    // Reset all cards to visible
    const quizCards = document.querySelectorAll('.quiz-card');
    quizCards.forEach(card => {
      card.style.display = '';
    });
  }
</script>

@endsection
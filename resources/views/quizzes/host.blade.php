@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full border-2 border-info">
      <div class="card-body">
        <div class="flex justify-start mb-2">
          <a href="{{ route('quizzes.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i>
          </a>
        </div>
        <div class="divider"></div>
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Quiz hosten: {{ $quiz->title }}</h2>
        
        <div class="flex flex-col gap-6">
          <div class="card bg-base-200 shadow-lg">
            <div class="card-body">
              <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-users text-xl"></i>
                <h3 class="font-bold text-xl">Spelers</h3>
              </div>
              <div id="players-container" class="flex flex-wrap gap-4 min-h-[100px]">
                <div class="flex flex-col items-center gap-2">
                  <i class="fas fa-user-slash text-2xl opacity-50"></i>
                  <p class="text-lg italic opacity-50">Nog geen spelers...</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/3">
              <img src="{{ $quiz->image }}" alt="Quiz Image" class="rounded-xl shadow-lg w-full">
            </div>
            <div class="w-full md:w-2/3">
              <div class="card bg-base-200 shadow-lg p-4">
                <h3 class="font-bold text-lg mb-2">Beschrijving:</h3>
                <p>{{ $quiz->description }}</p>
                
                @php
                  $tags = array_filter(array_map('trim', explode(',', $quiz->tags)));
                  $questionCount = $quiz->questions->count();
                @endphp
                
                <div class="mt-4">
                  <h3 class="font-bold text-lg mb-2">Tags:</h3>
                  <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                      <div class="badge badge-info">{{ $tag }}</div>
                    @endforeach
                  </div>
                </div>
                
                <div class="mt-4">
                  <h3 class="font-bold text-lg mb-2">Aantal vragen:</h3>
                  <div class="badge badge-error">{{ $questionCount }} {{ $questionCount === 1 ? 'vraag' : 'vragen' }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="divider"></div>
          
          <div class="flex flex-row gap-4 justify-between">
            <div class="flex flex-col gap-4">
              <h3 class="font-bold text-xl">Start de quiz:</h3>
              <div>
                <form action="{{ route('quizzes.start', $quiz->id) }}" method="POST">
                    @csrf
                    <button type="submit" id="start-button" class="btn btn-primary btn-lg opacity-50 cursor-not-allowed" disabled>
                        <i class="fa-solid fa-play mr-2"></i>
                        Start quiz sessie
                    </button>
                </form>
                <a href="{{ route('quizzes.questions.index', $quiz->id) }}" class="btn btn-outline btn-lg mt-2">
                  <i class="fa-solid fa-list-check mr-2"></i>
                  Bekijk vragen
                </a>
              </div>
            </div>
            <div class="card bg-base-200 shadow-lg border-2 border-info p-4 text-center">
              <h3 class="font-bold text-lg mb-2">Quiz Code:</h3>
              <p class="text-3xl font-bold font-mono mb-3">{{ $quizcode }}</p>
              
              <div class="flex justify-center">
                @php
                  $joinUrl = url("/join/" . $quizcode);
                @endphp
                <div class="w-40 h-40">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($joinUrl) }}" 
                       alt="Quiz QR Code" class="w-40 h-40">
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let currentPlayers = [];

function kickPlayer(name) {
  fetch(`/leave/{{ $quizcode }}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ name: name })
  })
  .then(() => {
    updatePlayers();
  });
}

function updatePlayers() {
  fetch(`/quizzes/{{ $quizcode }}/players`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
    .then(response => response.json())
    .then(players => {
      const container = document.getElementById('players-container');
      const startButton = document.getElementById('start-button');
      
      // Update start button state based on players
      if (players.length > 0) {
        startButton.disabled = false;
        startButton.classList.remove('opacity-50', 'cursor-not-allowed');
      } else {
        startButton.disabled = true;
        startButton.classList.add('opacity-50', 'cursor-not-allowed');
      }
      
      if (players.length === 0) {
        container.innerHTML = `
          <div class="flex flex-col items-center gap-2">
            <i class="fas fa-user-slash text-2xl opacity-50"></i>
            <p class="text-lg italic opacity-50">Nog geen spelers...</p>
          </div>
        `;
      } else {
        container.innerHTML = players.map(player => `
          <div class="bg-base-100 border-2 border-primary rounded-lg p-4 text-lg transition-all duration-300 ease-in-out flex items-center justify-between" 
               style="min-width: 200px;">
            <div class="flex items-center">
              <i class="fas fa-user mr-3"></i>
              ${player.name}
            </div>
            <button onclick="kickPlayer('${player.name}')" class="btn btn-ghost btn-sm text-error">
              <i class="fas fa-times"></i>
            </button>
          </div>
        `).join('');
      }
      
      currentPlayers = players;
    });
}

// Update initially and then every second
updatePlayers();
setInterval(updatePlayers, 1000);
</script>

@include('layouts.footer')
@endsection
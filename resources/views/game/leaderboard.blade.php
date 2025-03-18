@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-5xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
        <div class="flex flex-col items-center gap-6">
          <div class="flex items-center justify-center w-full">
            <h2 class="card-title text-center text-2xl sm:text-3xl md:text-4xl">
              🏆 Quiz Resultaten: {{ $quiz->title }}
            </h2>
          </div>
          
          <div class="divider"></div>
          
          <!-- Leaderboard -->
          <div class="w-full">
            <h3 class="text-xl font-bold mb-4 text-center">Eindstand</h3>
            
            @if(count($players) > 0)
              <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                  <thead>
                    <tr class="bg-base-300">
                      <th class="text-center">#</th>
                      <th>Speler</th>
                      <th class="text-center">Score</th>
                      <th class="text-center">Juiste antwoorden</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($players as $position => $player)
                      <tr class="{{ $position === 0 ? 'bg-warning/20' : '' }} {{ $position === 1 ? 'bg-neutral/20' : '' }} {{ $position === 2 ? 'bg-amber-200/20' : '' }}">
                        <td class="text-center font-bold">
                          @if($position === 0)
                            <span class="text-xl">🥇</span>
                          @elseif($position === 1)
                            <span class="text-xl">🥈</span>
                          @elseif($position === 2)
                            <span class="text-xl">🥉</span>
                          @else
                            {{ $position + 1 }}
                          @endif
                        </td>
                        <td class="font-bold">{{ $player['name'] }}</td>
                        <td class="text-center">{{ $player['score'] }}</td>
                        <td class="text-center">{{ $player['correct_answers'] }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="alert alert-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Geen spelers gevonden voor deze quiz.</span>
              </div>
            @endif
          </div>
          
          <div class="divider"></div>
          
          <!-- Quiz Stats -->
          <div class="w-full">
            <h3 class="text-xl font-bold mb-4 text-center">Quiz Statistieken</h3>
            
            <div class="stats shadow w-full">
              <div class="stat">
                <div class="stat-figure text-primary">
                  <i class="fas fa-users text-3xl"></i>
                </div>
                <div class="stat-title">Aantal spelers</div>
                <div class="stat-value">{{ count($players) }}</div>
              </div>
              
              <div class="stat">
                <div class="stat-figure text-secondary">
                  <i class="fas fa-question-circle text-3xl"></i>
                </div>
                <div class="stat-title">Aantal vragen</div>
                <div class="stat-value">{{ $quiz->questions()->count() }}</div>
              </div>
              
              <div class="stat">
                <div class="stat-figure text-accent">
                  <i class="fas fa-trophy text-3xl"></i>
                </div>
                <div class="stat-title">Winnaar</div>
                <div class="stat-value text-lg">{{ count($players) > 0 ? $players[0]['name'] : 'N/A' }}</div>
                <div class="stat-desc">Score: {{ count($players) > 0 ? $players[0]['score'] : 0 }}</div>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-4 mt-4">
            <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
              <i class="fas fa-home mr-2"></i>
              Terug naar Dashboard
            </a>
            
            <button onclick="window.print()" class="btn btn-outline">
              <i class="fas fa-print mr-2"></i>
              Print Resultaten
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('layouts.footer')
@endsection 
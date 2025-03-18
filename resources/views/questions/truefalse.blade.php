@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-full px-4 sm:px-6 rounded-lg">
    <div class="card bg-base-100 shadow-xl border-2 border-secondary">
    <div class="absolute inset-0 bg-base-100 -z-10 transform rotate-3 shadow-lg rounded-lg border-2 border-secondary/50"></div>
    <div class="absolute inset-0 bg-base-100 -z-20 transform -rotate-2 shadow-lg rounded-lg border-2 border-secondary/30"></div>
      <figure class="px-6 pt-6 max-w-2xl mx-auto">
        @if($question->image)
            <img src="{{ $question->image }}" alt="{{ $question->question }}" class="rounded-xl shadow-lg" />
        @endif
      </figure>
      <div class="card-body relative">
        <div class="flex justify-between items-center mb-4">
        <p class="text-xl opacity-75">Vraag 1 van {{ $question->quiz->questions->count() }}</p>
        <div id="divider" class="divider divider-horizontal mx-2"></div>
          <div id="timer-container" class="stats shadow">
            <div class="stat">
              <div class="stat-title">Resterende Tijd</div>
              <div class="stat-value">
                <span class="countdown font-mono text-3xl sm:text-4xl">
                  <span style="--value:{{ $question->time }};" aria-live="polite" aria-label="{{ $question->time }}">{{ $question->time }}</span>
                </span>
              </div>
              <div class="stat-desc">seconden</div>
            </div>
          </div>
        </div>
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">{{ $question->question }}</h2>
        <div class="divider mx-2"></div>
        <div id="answers-container" class="flex flex-col gap-4 justify-center">
          <button class="btn btn-primary btn-lg h-16 text-xl w-full transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]" onclick="selectAnswer('true')">Waar</button>
          <button class="btn btn-primary btn-lg h-16 text-xl w-full transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]" onclick="selectAnswer('false')">Niet waar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
let countdown = {{ $question->time }};
let selectedAnswer = null;
const countdownElement = document.querySelector('.countdown span');
const timerContainer = document.getElementById('timer-container');
const answersContainer = document.getElementById('answers-container');
const divider = document.getElementById('divider');

const timer = setInterval(() => {
    countdown--;
    countdownElement.style.setProperty('--value', countdown);
    countdownElement.setAttribute('aria-label', countdown);
    
    // Add error color when countdown is below 6
    if (countdown < 6) {
        countdownElement.parentElement.classList.add('text-error');
    }
    
    if (countdown <= 0) {
        clearInterval(timer);
        showResults();
    }
}, 1000);

function selectAnswer(answer) {
    selectedAnswer = answer;
    // Disable all buttons after selection
    const buttons = answersContainer.querySelectorAll('button');
    buttons.forEach(button => {
        button.disabled = true;
        if (button.onclick.toString().includes(answer)) {
            button.classList.add('btn-active');
        }
        button.style.opacity = '0';
        button.style.transform = 'translateY(20px)';
    });

    divider.style.opacity = '0';
    divider.style.transform = 'translateY(20px)';
    setTimeout(() => {
        // Clear answers container and add divider
        answersContainer.innerHTML = '';
        
        // Add timer and checkmark
        const resultDiv = document.createElement('div');
        resultDiv.className = 'flex flex-col items-center gap-4';
        
        resultDiv.appendChild(timerContainer);
        
        const checkmark = document.createElement('div');
        checkmark.className = 'w-full opacity-0 transform translate-y-4 transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] bg-success/80 rounded-lg p-4 flex items-center justify-center';
        checkmark.innerHTML = '<i class="fa-solid fa-circle-check text-white text-4xl"></i>';
        resultDiv.appendChild(checkmark);
        
        answersContainer.appendChild(resultDiv);

        // Trigger animation for checkmark after a brief delay
        setTimeout(() => {
            checkmark.style.opacity = '1';
            checkmark.style.transform = 'translateY(0)';
        }, 100);
    }, 500);
}

function showResults() {
    if (selectedAnswer === '{{ $question->answer }}') {
        alert('Correct! {{ $question->explanation }}');
    } else {
        alert('Incorrect. {{ $question->explanation }}');
    }
}
</script>
@include('layouts.footer')
@endsection

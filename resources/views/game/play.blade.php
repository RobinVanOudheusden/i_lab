@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card border-2 border-primary bg-base-100 shadow-xl w-full">
      <div class="card-body">
        <div class="flex flex-col items-center gap-6">
          <div class="flex justify-end w-full">
            <form action="{{ route('leave.quiz', $quizcode) }}" method="POST">
              @csrf
              <input type="hidden" name="name" value="{{ $name }}">
              <button type="submit" class="btn btn-error">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Verlaat quiz
              </button>
            </form>
          </div>

          <h2 class="card-title text-2xl sm:text-3xl md:text-4xl">👋 Welkom, {{ $name }}!</h2>
          <div id="waiting-status" class="flex flex-col items-center gap-2">
            <span class="loading loading-infinity loading-lg text-primary"></span>
            <p class="text-lg">Wachten tot de host start...</p>
          </div>
          <div id="started-status" class="flex flex-col items-center gap-2 hidden">
            <h3 id="current-question" class="text-xl font-bold mb-4"></h3>
            
            <!-- Container for question content -->
            <div id="question-container" class="w-full">
              <!-- Question templates will be loaded here -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<dialog id="kicked-modal" class="modal">
  <div class="modal-box border-2 border-error">
    <h3 class="font-bold text-lg">Je bent uit de quiz verwijderd</h3>
    <p class="py-4">De host heeft je uit deze quiz verwijderd.</p>
    <div class="modal-action">
      <form method="dialog">
        <a href="/" class="btn btn-primary">Terug naar home</a>
      </form>
    </div>
  </div>
</dialog>

<script>
function checkPlayerStatus() {
  fetch(`/quizzes/{{ $quizcode }}/players`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => response.json())
  .then(players => {
    const isStillInGame = players.some(player => player.name === '{{ $name }}');
    if (!isStillInGame) {
      document.getElementById('kicked-modal').showModal();
    }
  });
}

// Keep track of the current question ID to avoid resetting the UI unnecessarily
let currentQuestionId = null;
let hasAnsweredCurrentQuestion = false;
let quizHasStarted = false; // Track if the quiz has actually started

function checkIfStarted() {
  console.log('Checking quiz status for quizcode:', '{{ $quizcode }}');
  fetch(`/quizzes/{{ $quizcode }}/status`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => response.json())
  .then(data => {
    console.log('Quiz status response:', data);
    
    if (data.started) {
      quizHasStarted = true;
      document.getElementById('waiting-status').classList.add('hidden');
      document.getElementById('started-status').classList.remove('hidden');
    }
    
    // Only show end message if:
    // 1. The quiz has started
    // 2. We have a question (from the API or from the UI)
    // 3. The quiz is marked as ended
    const hasQuestion = data.has_current_question || currentQuestionId !== null;
    if (data.started && data.ended && hasQuestion) {
      console.log('Quiz has ended, showing end message');
      showQuizEndedMessage();
    }
  })
  .catch(error => {
    console.error('Error checking quiz status:', error);
  });
}

function showQuizEndedMessage() {
  // Clear any existing content and show the quiz ended message
  const questionContainer = document.getElementById('question-container');
  questionContainer.innerHTML = '';
  
  // Create the quiz ended content
  const endedDiv = document.createElement('div');
  endedDiv.className = 'card bg-base-100 shadow-xl border-2 border-primary w-full p-6';
  endedDiv.innerHTML = `
    <div class="flex flex-col items-center gap-6 text-center">
      <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center">
        <i class="fas fa-trophy text-4xl text-white"></i>
      </div>
      <h3 class="text-2xl font-bold">Quiz voltooid!</h3>
      <p class="text-lg">De quiz is afgelopen. Bedankt voor je deelname!</p>
      <p class="opacity-70">De host kan je score en de eindstand laten zien.</p>
      <a href="/" class="btn btn-primary btn-lg mt-4">
        <i class="fas fa-home mr-2"></i>
        Terug naar Home
      </a>
    </div>
  `;
  
  // Set the current question text
  document.getElementById('current-question').textContent = 'Quiz voltooid';
  
  // Add to the container
  questionContainer.appendChild(endedDiv);
  
  // Clear any active intervals
  if (window.timerInterval) clearInterval(window.timerInterval);
  if (window.waitForNextInterval) clearInterval(window.waitForNextInterval);
}

function checkCurrentQuestion() {
  fetch(`/quizzes/{{ $quizcode }}/current`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => response.json())
  .then(data => {
    // If there's a question data
    if (data.question) {
      const question = data.question;
      
      // Only update the UI if this is a new question
      if (currentQuestionId !== question.id) {
        console.log('New question detected, updating UI');
        currentQuestionId = question.id;
        hasAnsweredCurrentQuestion = false;
        
        // Clear previous question content
        const questionContainer = document.getElementById('question-container');
        questionContainer.innerHTML = '';
        
        // Display the question number as sequence number, not ID
        const questionNumber = parseInt(data.current_question);
        document.getElementById('current-question').textContent = `Vraag ${questionNumber}`;
        
        // Create a div to hold the included template
        const templateContainer = document.createElement('div');
        
        // Handle question based on type
        if (question.type === 'multiple_choice' || question.type === 'multiplechoice') {
          console.log('Processing multiplechoice question:', question);
          console.log('Options array:', question.options);
          
          // Build the HTML directly for faster rendering
          templateContainer.innerHTML = `
            <div class="card bg-base-100 shadow-xl border-2 border-secondary w-full">
              ${question.image ? `
                <figure class="px-6 pt-6 max-w-2xl mx-auto">
                  <img src="${question.image}" alt="${question.question}" class="rounded-xl shadow-lg">
                </figure>
              ` : ''}
              <div class="card-body">
                <h4 class="text-xl font-bold mb-4">${question.question}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4" id="options-container">
                  ${Array.isArray(question.options) ? 
                    question.options.map((option, index) => 
                      `<button class="btn btn-primary btn-lg h-16 text-xl option-button" data-option="${option}">${option}</button>`
                    ).join('') : 'Options not available'}
                </div>
                <div class="mt-4 flex justify-center">
                  <div class="radial-progress text-primary timer" style="--value:100; --size:5rem;">
                    <span id="countdown-text">${question.time}</span>
                  </div>
                </div>
              </div>
            </div>
          `;
          
          questionContainer.appendChild(templateContainer);
          
          // Set up event listeners for the buttons
          const optionButtons = templateContainer.querySelectorAll('.option-button');
          optionButtons.forEach(button => {
            button.addEventListener('click', () => {
              submitAnswer(button.dataset.option);
              // Disable all buttons after selection to prevent multiple submissions
              optionButtons.forEach(btn => btn.disabled = true);
            });
          });
          
          // Set up the timer
          if (question.time) {
            setupTimer(question.time, templateContainer.querySelector('.timer'));
          }
          
        } else if (question.type === 'true_false' || question.type === 'truefalse') {
          // Build the HTML directly for faster rendering
          templateContainer.innerHTML = `
            <div class="card bg-base-100 shadow-xl border-2 border-secondary w-full">
              ${question.image ? `
                <figure class="px-6 pt-6 max-w-2xl mx-auto">
                  <img src="${question.image}" alt="${question.question}" class="rounded-xl shadow-lg">
                </figure>
              ` : ''}
              <div class="card-body">
                <h4 class="text-xl font-bold mb-4">${question.question}</h4>
                <div class="flex flex-col md:flex-row gap-4 justify-center my-4">
                  <button class="btn btn-success btn-lg w-full md:w-1/3 true-button">Waar</button>
                  <button class="btn btn-error btn-lg w-full md:w-1/3 false-button">Niet waar</button>
                </div>
                <div class="mt-4 flex justify-center">
                  <div class="radial-progress text-primary timer" style="--value:100; --size:5rem;">
                    <span id="countdown-text">${question.time}</span>
                  </div>
                </div>
              </div>
            </div>
          `;
          
          questionContainer.appendChild(templateContainer);
          
          // Set up event listeners for true/false buttons
          const trueButton = templateContainer.querySelector('.true-button');
          const falseButton = templateContainer.querySelector('.false-button');
          
          if (trueButton) trueButton.addEventListener('click', () => {
            submitAnswer('true');
            // Disable buttons after selection
            trueButton.disabled = true;
            falseButton.disabled = true;
          });
          
          if (falseButton) falseButton.addEventListener('click', () => {
            submitAnswer('false');
            // Disable buttons after selection
            trueButton.disabled = true;
            falseButton.disabled = true;
          });
          
          // Set up the timer
          if (question.time) {
            setupTimer(question.time, templateContainer.querySelector('.timer'));
          }
        }
      }
    }
  })
  .catch(error => {
    console.error('Error fetching current question:', error);
  });
}

function setupTimer(time, timerElement) {
  if (!timerElement) return;
  
  // Reset timer
  timerElement.style.setProperty('--value', '100');
  
  // Track current time for visual display
  let currentTime = time;
  const countdownText = timerElement.querySelector('#countdown-text');
  if (countdownText) countdownText.textContent = currentTime;
  
  // Calculate interval step based on time (seconds)
  const totalTime = time;
  const intervalStep = 100 / (totalTime * 10);
  let remainingPercentage = 100;
  
  // Clear any existing interval
  if (window.timerInterval) clearInterval(window.timerInterval);
  
  // Create new timer interval
  window.timerInterval = setInterval(() => {
    remainingPercentage -= intervalStep;
    timerElement.style.setProperty('--value', remainingPercentage.toFixed(0));
    
    // Update countdown text every second
    if (remainingPercentage % 10 === 0) {
      currentTime = Math.floor(remainingPercentage / 100 * totalTime);
      if (countdownText) countdownText.textContent = currentTime;
    }
    
    // Add warning color when time is running low
    if (remainingPercentage < 30) {
      timerElement.classList.remove('text-primary');
      timerElement.classList.add('text-error');
    }
    
    // Stop when time is up
    if (remainingPercentage <= 0) {
      clearInterval(window.timerInterval);
      submitAnswer(''); // Submit empty answer when time's up
    }
  }, 100); // Update every 100ms for smoother animation
}

function submitAnswer(answer) {
  // Stop the timer but keep a visual indication
  if (window.timerInterval) {
    clearInterval(window.timerInterval);
    
    // Show that time is paused but don't freeze the UI entirely
    const timerElement = document.querySelector('.timer');
    if (timerElement) {
      timerElement.classList.remove('text-primary', 'text-error');
      timerElement.classList.add('text-secondary', 'opacity-70');
    }
  }
  
  // Prevent multiple submissions for the same question
  if (hasAnsweredCurrentQuestion) return;
  hasAnsweredCurrentQuestion = true;
  
  // Submit the player's answer
  fetch(`/quizzes/{{ $quizcode }}/answer`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      name: '{{ $name }}',
      answer: answer,
      question_id: currentQuestionId // Send the question ID to properly track answers
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log('Answer submitted:', data);
    // Show feedback to the user
    showAnswerFeedback(answer);
    
    // Continue checking for the next question
    // The poll interval for next question can be longer since we're just waiting
    if (window.waitForNextInterval) clearInterval(window.waitForNextInterval);
    window.waitForNextInterval = setInterval(() => {
      checkCurrentQuestion();
    }, 2000); // Check every 2 seconds for new questions
  })
  .catch(error => {
    console.error('Error submitting answer:', error);
    
    // If submission fails, allow retrying
    hasAnsweredCurrentQuestion = false;
    
    // Show error message
    const questionContainer = document.getElementById('question-container');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'mt-4 p-4 bg-error/20 text-error rounded-lg text-center';
    errorDiv.textContent = 'Er ging iets mis bij het versturen van je antwoord. Probeer het opnieuw.';
    questionContainer.appendChild(errorDiv);
  });
}

function showAnswerFeedback(answer) {
  // Get the question container
  const questionContainer = document.getElementById('question-container');
  
  // Don't duplicate feedback if it already exists
  if (questionContainer.querySelector('.answer-feedback')) {
    return;
  }
  
  // Add a feedback element at the bottom of the question
  const feedbackDiv = document.createElement('div');
  feedbackDiv.className = 'mt-6 p-4 bg-base-200 rounded-lg text-center answer-feedback';
  
  // Show a loading animation while waiting for the next question
  feedbackDiv.innerHTML = `
    <h4 class="text-xl font-bold mb-2">Antwoord verzonden!</h4>
    <p>Je hebt geantwoord: <span class="font-bold">${answer || 'Geen antwoord (tijd verstreken)'}</span></p>
    <div class="flex flex-col items-center gap-2 mt-4">
      <span class="loading loading-dots loading-md text-secondary"></span>
      <p class="text-secondary">Wacht tot de host naar de volgende vraag gaat...</p>
    </div>
  `;
  
  // Find the card body to add the feedback to
  const cardBody = questionContainer.querySelector('.card-body');
  if (cardBody) {
    cardBody.appendChild(feedbackDiv);
  } else {
    questionContainer.appendChild(feedbackDiv);
  }
  
  // Disable all option buttons to prevent further clicks
  const optionButtons = questionContainer.querySelectorAll('button');
  optionButtons.forEach(button => {
    button.disabled = true;
    button.classList.add('opacity-50');
  });
  
  // If the answered option is known, highlight it
  if (answer && answer !== '') {
    const optionButtons = questionContainer.querySelectorAll('[data-option]');
    optionButtons.forEach(button => {
      if (button.dataset.option === answer) {
        button.classList.remove('opacity-50');
        button.classList.add('btn-secondary', 'border-2', 'border-secondary');
      }
    });
    
    // For true/false questions
    if (answer === 'true') {
      const trueButton = questionContainer.querySelector('.true-button');
      if (trueButton) {
        trueButton.classList.remove('opacity-50');
        trueButton.classList.add('btn-secondary', 'border-2', 'border-secondary');
      }
    } else if (answer === 'false') {
      const falseButton = questionContainer.querySelector('.false-button');
      if (falseButton) {
        falseButton.classList.remove('opacity-50');
        falseButton.classList.add('btn-secondary', 'border-2', 'border-secondary');
      }
    }
  }
}

// Check player status initially and then every second
checkPlayerStatus();
setInterval(checkPlayerStatus, 1000);

// Check if game started initially and then every second
checkIfStarted();
setInterval(checkIfStarted, 1000);

// Check current question initially and then every second
checkCurrentQuestion();
setInterval(checkCurrentQuestion, 1000);
</script>


@endsection
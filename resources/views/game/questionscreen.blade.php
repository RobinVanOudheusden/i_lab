@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-5xl px-4 sm:px-6 rounded-lg">
    <div class="card bg-base-100 shadow-xl border-2 border-secondary w-full">
      <div class="card-body p-4">
        <div class="flex flex-wrap justify-between items-center">
          <p class="text-xl font-bold">Vraag <span id="current-question">1</span> van {{ $Question->quiz->questions->count() }}</p>
          <div class="flex gap-2">
            <button onclick="endQuiz()" class="btn btn-error">
              <i class="fas fa-stop mr-2"></i>
              Beëindig quiz
            </button>
          </div>
        </div>
      </div>
      <div class="divider my-0"></div>
      
      <div class="card-body relative">
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6 text-center">{{ $Question->question }}</h2>
        
        @if($Question->image)
        <figure class="max-w-xl mx-auto mb-6">
          <img src="{{ $Question->image }}" alt="{{ $Question->question }}" class="rounded-xl shadow-lg w-full" />
        </figure>
        @endif
        
        <!-- Question options display -->
        @if($Question->type === 'multiplechoice' || $Question->type === 'multiple_choice')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          @php
            $options = json_decode($Question->options);
          @endphp
          @foreach($options as $option)
            <div class="stat bg-base-200 shadow-lg rounded-lg border-2 border-base-300 relative overflow-hidden answer-stat hover:shadow-xl transition-all duration-300" data-option="{{ $option }}">
              <div class="absolute inset-0 bg-primary opacity-0 answer-progress transition-all duration-500" style="width: 0%"></div>
              <div class="stat-title relative z-10 font-semibold text-lg">{{ $option }}</div>
              <div class="stat-value relative z-10 flex items-baseline gap-2">
                <span class="answer-count text-3xl">0</span>
                <span class="text-sm opacity-80">spelers</span>
              </div>
              <div class="stat-desc relative z-10 text-base">
                <span class="answer-percent font-bold">0</span>
                <span class="opacity-80">% van de antwoorden</span>
              </div>
              @if($option == $Question->correct_option)
                <div class="absolute top-2 right-2 opacity-0 text-success hidden transform scale-0 transition-transform duration-500" id="correct-indicator-{{ str_replace(' ', '_', $option) }}">
                  <i class="fas fa-check-circle text-2xl"></i>
                </div>
              @endif
            </div>
          @endforeach
        </div>
        @elseif($Question->type === 'truefalse' || $Question->type === 'true_false')
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="stat bg-base-200 shadow-lg rounded-lg border-2 border-base-300 relative overflow-hidden answer-stat flex-1 hover:shadow-xl transition-all duration-300" data-option="true">
            <div class="absolute inset-0 bg-primary opacity-0 answer-progress transition-all duration-500" style="width: 0%"></div>
            <div class="stat-title relative z-10 font-semibold text-lg">Waar</div>
            <div class="stat-value relative z-10 flex items-baseline gap-2">
              <span class="answer-count text-3xl">0</span>
              <span class="text-sm opacity-80">spelers</span>
            </div>
            <div class="stat-desc relative z-10 text-base">
              <span class="answer-percent font-bold">0</span>
              <span class="opacity-80">% van de antwoorden</span>
            </div>
            @if($Question->correct_option == 'true')
              <div class="absolute top-2 right-2 opacity-0 text-success hidden transform scale-0 transition-transform duration-500" id="correct-indicator-true">
                <i class="fas fa-check-circle text-2xl"></i>
              </div>
            @endif
          </div>
          <div class="stat bg-base-200 shadow-lg rounded-lg border-2 border-base-300 relative overflow-hidden answer-stat flex-1 hover:shadow-xl transition-all duration-300" data-option="false">
            <div class="absolute inset-0 bg-primary opacity-0 answer-progress transition-all duration-500" style="width: 0%"></div>
            <div class="stat-title relative z-10 font-semibold text-lg">Niet waar</div>
            <div class="stat-value relative z-10 flex items-baseline gap-2">
              <span class="answer-count text-3xl">0</span>
              <span class="text-sm opacity-80">spelers</span>
            </div>
            <div class="stat-desc relative z-10 text-base">
              <span class="answer-percent font-bold">0</span>
              <span class="opacity-80">% van de antwoorden</span>
            </div>
            @if($Question->correct_option == 'false')
              <div class="absolute top-2 right-2 opacity-0 text-success hidden transform scale-0 transition-transform duration-500" id="correct-indicator-false">
                <i class="fas fa-check-circle text-2xl"></i>
              </div>
            @endif
          </div>
        </div>
        @endif
        
        <div class="w-full bg-base-200 rounded-full h-6 mb-6">
          <div id="timer-progress" class="bg-primary h-full rounded-full transition-all duration-1000" style="width: 100%"></div>
        </div>
        
        <div class="text-center mb-4">
          <span id="timer-text" class="text-4xl font-bold font-mono">{{ $Question->time }}</span>
          <span class="text-xl"> seconden</span>
        </div>
        
      <div class="card-footer p-4 bg-base-200 rounded-b-lg">
        <div class="flex justify-between items-center">
          <button id="show-answers-btn" type="button" class="btn btn-primary btn-disabled" disabled>
            <i class="fas fa-check-circle mr-2"></i>
            Toon antwoorden
          </button>
          
          <button id="next-question-btn" type="button" class="btn btn-accent btn-disabled" disabled>
            <span>Volgende vraag</span>
            <i class="fas fa-arrow-right ml-2"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let countdown = {{ $Question->time }};
let totalPlayers = 0;
let playersAnswered = 0;
let playerAnswers = {};
const progressBar = document.getElementById('timer-progress');
const timerText = document.getElementById('timer-text');
const showAnswersBtn = document.getElementById('show-answers-btn');
const nextQuestionBtn = document.getElementById('next-question-btn');

// Ensure buttons are initialized correctly
showAnswersBtn.disabled = true;
showAnswersBtn.classList.add('btn-disabled');
nextQuestionBtn.disabled = true;
nextQuestionBtn.classList.add('btn-disabled');

// Update total players count and their answers
function updatePlayers() {
  fetch(`/quizzes/{{ $Question->quiz->id }}/players`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => response.json())
  .then(players => {
    totalPlayers = players.length;
    document.getElementById('total-players').textContent = totalPlayers;
    
    // Now fetch player answers for current question
    fetchPlayerAnswers();
  });
}

// Fetch actual player answers from the server
function fetchPlayerAnswers() {
  fetch(`/quizzes/{{ $Question->quiz->id }}/answers/{{ $Question->id }}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => response.json())
  .then(answers => {
    // Debug: Log the actual API response
    console.log('Player answers API response:', answers);
    
    // Count answers by option
    playerAnswers = {};
    playersAnswered = answers.length;
    
    // Get all options from the DOM and sanitize them for comparison
    const allOptions = {};
    const sanitizedOptions = {}; // Map sanitized options to original options
    
    document.querySelectorAll('.answer-stat').forEach(stat => {
      const option = stat.dataset.option;
      console.log('Available option in DOM:', option);
      
      // Create sanitized version of option (no special chars, lowercase)
      const sanitizedOption = sanitizeString(option);
      
      allOptions[option] = true;
      sanitizedOptions[sanitizedOption] = option;
      
      playerAnswers[option] = {
        count: 0
      };
    });
    
    // Count actual answers with more debugging
    answers.forEach(answer => {
      // Sanitize answer to ensure proper comparison
      const originalAnswer = String(answer.answer);
      const sanitizedAnswer = sanitizeString(originalAnswer);
      
      console.log('Processing answer:', answer, 'sanitized:', sanitizedAnswer);
      
      // Try direct match first
      if (playerAnswers[originalAnswer] !== undefined) {
        console.log('Direct match found for:', originalAnswer);
        playerAnswers[originalAnswer].count++;
      } 
      // Try sanitized match
      else if (sanitizedOptions[sanitizedAnswer]) {
        const matchedOption = sanitizedOptions[sanitizedAnswer];
        console.log('Sanitized match found:', sanitizedAnswer, '→', matchedOption);
        playerAnswers[matchedOption].count++;
      }
      // No match found
      else {
        console.warn('No match found for answer:', originalAnswer, 'sanitized:', sanitizedAnswer);
        
        // Try a more lenient approach - check if any option contains this answer
        let found = false;
        Object.keys(allOptions).forEach(option => {
          if (option.includes(originalAnswer) || originalAnswer.includes(option)) {
            console.log('Partial match found:', originalAnswer, '~', option);
            playerAnswers[option].count++;
            found = true;
          }
        });
        
        if (!found) {
          console.error('No match found for this answer by any method');
        }
      }
    });
    
    console.log('Final playerAnswers:', playerAnswers);
    updateAnswerStats();
    updatePlayerAnsweredStats();
  })
  .catch(error => {
    console.error('Error fetching player answers:', error);
    // Fall back to simulation if endpoint not available
    simulatePlayerAnswers();
  });
}

// Helper function to sanitize strings for comparison
function sanitizeString(str) {
  if (!str) return '';
  return String(str)
    .trim()
    .toLowerCase()
    .replace(/[\r\n\t]/g, '') // Remove carriage returns, newlines, tabs
    .replace(/\s+/g, ' ');    // Normalize spaces
}

// Update the answer statistics UI
function updateAnswerStats() {
  const answerStats = document.querySelectorAll('.answer-stat');
  console.log('Updating answer stats:', answerStats.length, 'options found');
  
  // Calculate total answers for accurate percentages
  let totalAnswers = 0;
  Object.values(playerAnswers).forEach(answer => {
    totalAnswers += answer.count;
  });
  
  answerStats.forEach(stat => {
    const option = stat.dataset.option;
    console.log('Updating stats for option:', option, 'count:', playerAnswers[option]?.count);
    const count = playerAnswers[option]?.count || 0;
    // Use total answers instead of players answered for more accurate percentage
    const percent = totalAnswers > 0 ? Math.round((count / totalAnswers) * 100) : 0;
    
    // Update the counts and percentages with more visible numbers
    stat.querySelector('.answer-count').textContent = count;
    
    // Make non-zero counts stand out more
    if (count > 0) {
      stat.querySelector('.answer-count').style.color = 'var(--p)'; // Primary color
      stat.querySelector('.answer-count').style.fontWeight = 'bold';
      
      // Add a more noticeable background for options with answers
      if (percent === 100) {
        stat.style.backgroundColor = 'rgba(var(--p), 0.1)'; // Light primary for 100%
        stat.style.borderColor = 'var(--p)';                // Primary border
      } else if (percent > 0) {
        stat.style.backgroundColor = 'rgba(var(--p), 0.05)'; // Very light primary
      }
    } else {
      stat.querySelector('.answer-count').style.color = '';
      stat.querySelector('.answer-count').style.fontWeight = '';
      stat.style.backgroundColor = '';
      stat.style.borderColor = '';
    }
    
    stat.querySelector('.answer-percent').textContent = percent;
    
    // Make progress bars much more visible
    const progressBar = stat.querySelector('.answer-progress');
    progressBar.style.width = `${percent}%`;
    progressBar.style.opacity = '0.6'; // Much higher opacity for better visibility
    progressBar.style.transition = 'width 0.5s ease-in-out, opacity 0.5s ease-in-out';
    
    // Apply a more noticeable background color to the progress bar
    if (count > 0) {
      progressBar.style.backgroundColor = 'var(--p)'; // Primary color progress bar
    }
    
    // Remove any existing player lists as we don't need to show names
    const existingList = stat.querySelector('.players-list');
    if (existingList) {
      existingList.remove();
    }
  });
  
  // Force a repaint to ensure progress bars update
  document.body.offsetHeight;
}

// Simulate players answering over time (as a fallback)
function simulatePlayerAnswers() {
  // Only used if the real answer endpoint isn't available
  if (playersAnswered < totalPlayers && countdown > 0) {
    // Simulate more players answering as time passes
    playersAnswered = Math.min(totalPlayers, Math.floor((totalPlayers * (1 - countdown / {{ $Question->time }})) + Math.random() * 2));
    
    // Distribute answers randomly across options
    const answerStats = document.querySelectorAll('.answer-stat');
    if (answerStats.length > 0) {
      // Reset all counts
      playerAnswers = {};
      answerStats.forEach(stat => {
        playerAnswers[stat.dataset.option] = {
          count: 0
        };
      });
      
      // Distribute answers
      for (let i = 0; i < playersAnswered; i++) {
        const randomIndex = Math.floor(Math.random() * answerStats.length);
        const option = answerStats[randomIndex].dataset.option;
        playerAnswers[option].count++;
      }
      
      updateAnswerStats();
    }
    
    updatePlayerAnsweredStats();
  }
}

function updatePlayerAnsweredStats() {
  const percentage = totalPlayers > 0 ? Math.round((playersAnswered / totalPlayers) * 100) : 0;
  document.querySelector('#players-answered .stat-value').textContent = `${playersAnswered} / ${totalPlayers}`;
  document.querySelector('#players-answered .stat-desc').textContent = `${percentage}%`;
  
  // Enable the show answers button if at least one person has answered
  if (playersAnswered > 0 && countdown <= 0) {
    showAnswersBtn.classList.remove('btn-disabled');
    showAnswersBtn.disabled = false;
  }
}

const timer = setInterval(() => {
    countdown--;
    timerText.textContent = countdown;
    
    // Update progress bar smoothly
    const progressWidth = (countdown / {{ $Question->time }}) * 100;
    progressBar.style.width = progressWidth + '%';
    
    // Update player answers
    fetchPlayerAnswers();
    
    if (countdown < 6) {
        progressBar.classList.remove('bg-primary');
        progressBar.classList.add('bg-error');
        timerText.classList.add('text-error');
    }

    if (countdown <= 0) {
        clearInterval(timer);
        
        // Enable buttons after time is up
        showAnswersBtn.classList.remove('btn-disabled');
        showAnswersBtn.disabled = false;
        
        // Make POST request when timer reaches 0
        showAnswers();
    }
}, 1000);

function showAnswers() {
    const url = `{{ route('quizzes.show-answers', ['quizcode' => $Question->quiz->id]) }}`;
    
    fetch(url, {
        method: 'POST', 
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Enable next question button
        nextQuestionBtn.classList.remove('btn-disabled');
        nextQuestionBtn.disabled = false;
        
        // Show correct answers
        showCorrectAnswers();
        
        // Apply the styling again after a short delay to ensure it takes effect
        setTimeout(showCorrectAnswers, 500);
        
        // Remove any existing event listeners to prevent duplicates
        nextQuestionBtn.removeEventListener('click', goToNextQuestion);
        
        // Add click handler to next question button
        nextQuestionBtn.addEventListener('click', function(event) {
            goToNextQuestion(event);
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showCorrectAnswers() {
    console.log('Running showCorrectAnswers function');
    
    // Highlight the correct answer
    document.querySelectorAll('[id^="correct-indicator"]').forEach(indicator => {
        console.log('Found correct indicator:', indicator.id);
        indicator.classList.remove('hidden');
        indicator.classList.add('flex');
        indicator.style.display = 'flex';
        indicator.style.opacity = '1';
        indicator.style.transform = 'scale(1)';
    });
    
    // Add a visual effect to the correct answer stat
    const correctOption = '{{ $Question->correct_option }}';
    console.log('Correct option is:', correctOption);
    
    document.querySelectorAll('.answer-stat').forEach(stat => {
        const option = stat.dataset.option;
        console.log(`Checking option: ${option} against correct: ${correctOption}`);
        
        // First reset any previous styling that might interfere
        stat.style.transform = '';
        stat.style.zIndex = '';
        stat.style.boxShadow = '';
        stat.style.opacity = '';
        stat.style.border = '';
        stat.style.backgroundColor = '';
        
        // Remove any previous "CORRECT" label
        const existingLabel = stat.querySelector('.correct-label');
        if (existingLabel) {
            existingLabel.remove();
        }
        
        // Try both sanitized and original versions for comparison
        const isCorrect = 
            String(option) === String(correctOption) || 
            sanitizeString(option) === sanitizeString(correctOption);
            
        if (isCorrect) {
            console.log('Found the correct option element:', option);
            
            // Reset all existing styles first to prevent conflicts
            stat.removeAttribute('style');
            
            // Clear existing border classes
            stat.classList.remove('border-base-300');
            
            // First restore original position and z-index
            stat.style.position = 'relative';
            stat.style.zIndex = '10';
            
            // Apply explicit green dotted border
            stat.style.border = '6px dotted #36D399';
            
            // Add a checkmark indicator if it doesn't exist
            let indicator = stat.querySelector('[id^="correct-indicator"]');
            if (indicator) {
                indicator.style.color = '#36D399';
                indicator.style.opacity = '1';
                indicator.style.display = 'block';
            } else {
                // Create indicator if it doesn't exist
                indicator = document.createElement('div');
                indicator.innerHTML = '<i class="fas fa-check-circle text-2xl"></i>';
                indicator.style.position = 'absolute';
                indicator.style.top = '8px';
                indicator.style.right = '8px';
                indicator.style.color = '#36D399';
                stat.appendChild(indicator);
            }
            
            // Progress bar keeps its primary color for correct answer
            const progress = stat.querySelector('.answer-progress');
            if (progress) {
                progress.style.backgroundColor = 'var(--p)';
            }
        } else {
            // Only reduce opacity for incorrect answers, keep original color
            stat.style.opacity = '0.5';
            
            // Progress bar should keep its existing color/styling for wrong answers
            const progress = stat.querySelector('.answer-progress');
            if (progress) {
                // Keep existing color but match opacity
                progress.style.opacity = '0.5';
            }
        }
    });
    
    // Reapply the dotted border after a short delay to ensure it's visible
    setTimeout(() => {
        const correctStat = Array.from(document.querySelectorAll('.answer-stat')).find(stat => {
            const option = stat.dataset.option;
            return String(option) === String('{{ $Question->correct_option }}') || 
                   sanitizeString(option) === sanitizeString('{{ $Question->correct_option }}');
        });
        
        if (correctStat) {
            console.log('Reinforcing border on correct answer');
            correctStat.style.border = '6px dotted #36D399';
        }
    }, 300);
}

function goToNextQuestion(event) {
    // Prevent default behavior to avoid form submission
    if (event) {
        event.preventDefault();
    }
    
    // Disable the button to prevent double-clicks
    const nextQuestionBtn = document.getElementById('next-question-btn');
    if (nextQuestionBtn) {
        nextQuestionBtn.disabled = true;
        nextQuestionBtn.classList.add('btn-disabled');
        nextQuestionBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span> Laden...';
    }
    
    // Call the API to get the next question
    fetch(`/quizzes/{{ $Question->quiz->id }}/next-question`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.is_completed) {
            // Quiz is completed, go to leaderboard
            showQuizCompletedMessage();
        } else {
            // Use the showQuestionsScreen method which is GET-accessible
            window.location.href = `/quizzes/{{ $Question->quiz->id }}/question-screen`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Er is een fout opgetreden bij het laden van de volgende vraag. Probeer het opnieuw.');
        
        // Re-enable the button in case of error
        if (nextQuestionBtn) {
            nextQuestionBtn.disabled = false;
            nextQuestionBtn.classList.remove('btn-disabled');
            nextQuestionBtn.innerHTML = '<span>Volgende vraag</span><i class="fas fa-arrow-right ml-2"></i>';
        }
    });
    
    // Return false to prevent default action
    return false;
}

function showQuizCompletedMessage() {
    // Show a modal with completion message
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50';
    modal.innerHTML = `
        <div class="bg-base-100 p-8 rounded-lg shadow-lg max-w-md w-full">
            <h3 class="text-2xl font-bold mb-4 text-primary">Quiz voltooid! 🎉</h3>
            <p class="mb-6">Alle vragen zijn voltooid. Bekijk de resultaten op het scorebord.</p>
            <div class="flex justify-end">
                <a href="/quizzes/{{ $Question->quiz->id }}/leaderboard" class="btn btn-primary">
                    <i class="fas fa-trophy mr-2"></i>
                    Bekijk scorebord
                </a>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // After a short delay, redirect to the leaderboard
    setTimeout(() => {
        window.location.href = "/quizzes/{{ $Question->quiz->id }}/leaderboard";
    }, 3000);
}

function endQuiz() {
    if (confirm('Weet je zeker dat je de quiz wilt beëindigen? Dit brengt je terug naar het dashboard.')) {
        window.location.href = "{{ route('quizzes.index') }}";
    }
}

// Initial updates
updatePlayers();
setInterval(updatePlayers, 3000); // Update player count every 3 seconds
</script>

@include('layouts.footer')
@endsection
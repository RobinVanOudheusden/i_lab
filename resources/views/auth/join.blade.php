@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card border-2 border-primary bg-base-100 shadow-xl w-full max-w-md">
      <div class="card-body">
        <h2 class="card-title text-lg sm:text-xl md:text-2xl mb-6 text-center font-mono mx-auto">Voer de quizcode in:</h2>
        
        <form method="POST" action="{{ route('quizzes.join.submit') }}" class="space-y-4" id="join-form">
          @csrf
          
          <div class="form-control">
            <div class="flex justify-center gap-3">
              @php
                // Get code from URL parameter if available
                $code = $code ?? request()->get('code', '');
              @endphp
              @for ($i = 0; $i < 5; $i++)
                <input type="text" 
                      class="w-14 h-16 text-center font-mono text-2xl font-bold input input-bordered transition-all duration-300 focus:scale-90 focus:w-12 focus:h-12 @error('quizcode') input-error @enderror" 
                      maxlength="1" 
                      inputmode="numeric" 
                      pattern="[0-9]"
                      data-index="{{ $i }}"
                      value="{{ strlen($code) > $i ? $code[$i] : '' }}"
                      oninput="handleCodeInput(this)"
                      onkeydown="handleKeyDown(event, this)"
                      onfocus="handleFocus(this)"
                      onblur="handleBlur(this)">
              @endfor
            </div>
            <input type="hidden" name="quizcode" id="quizcode-hidden" value="{{ $code ?: old('quizcode') }}">
            <input type="hidden" name="player_name" id="player-name-hidden">
            @error('quizcode')
              <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </label>
            @enderror
            <div id="code-status" class="mt-2 text-center hidden">
              <span class="loading loading-spinner loading-md text-secondary"></span>
              <p class="text-secondary text-sm">Controleren...</p>
            </div>
          </div>
        <div class="divider"></div>
          
          <div class="form-control mt-8">
            <button type="submit" class="btn btn-primary btn-lg shadow-md hover:shadow-lg transition-all duration-300" id="join-button">
              <i class="fa-solid fa-play mr-2"></i>
              Controleer code
            </button>
          </div>
          
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Name Modal -->
<dialog id="name-modal" class="modal">
  <div class="modal-box border-2 border-primary">
    <h3 class="font-bold text-lg mb-4" id="modal-quiz-title"></h3>
    <div class="form-control">
      <div class="flex flex-col gap-2">
        <div class="flex items-center gap-4">
          <input type="text" 
                 id="player-name-input"
                 class="input input-bordered w-full" 
                 placeholder="Je naam"
                 maxlength="16">
          <span id="char-count" class="text-sm text-base-content/70">0/16</span>
        </div>
      </div>
    </div>
    <div class="modal-action">
      <button class="btn btn-primary" onclick="submitName()">Deelnemen</button>
      <button class="btn" onclick="closeNameModal()">Annuleren</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

<!-- Error Modal -->
<dialog id="error-modal" class="modal">
  <div class="modal-box border-2 border-error">
    <h3 class="font-bold text-lg mb-4">Fout</h3>
    <p id="error-message" class="text"></p>
    <div class="modal-action">
      <button class="btn" onclick="closeErrorModal()">Sluiten</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set initial values if there's old input
    const oldValue = "{{ old('quizcode') }}";
    if (oldValue) {
      const inputs = document.querySelectorAll('[data-index]');
      for (let i = 0; i < oldValue.length && i < 5; i++) {
        inputs[i].value = oldValue[i];
        if (inputs[i].value) {
          updateActiveInputs(i + 1);
        }
      }
    }
    
    // Focus the first empty input
    const inputs = document.querySelectorAll('[data-index]');
    for (let i = 0; i < inputs.length; i++) {
      if (!inputs[i].value) {
        inputs[i].focus();
        break;
      }
    }
    
    // Add submit event listener to check code before submission
    document.getElementById('join-form').addEventListener('submit', function(e) {
      e.preventDefault();
      checkQuizCode();
    });

    // Add input event listener for player name
    document.getElementById('player-name-input').addEventListener('input', function(e) {
      const value = e.target.value;
      document.getElementById('char-count').textContent = `${value.length}/16`;
    });
  });

  function handleCodeInput(input) {
    // Only allow numbers
    input.value = input.value.replace(/[^0-9]/g, '');
    
    // Update hidden field
    updateHiddenField();
    
    // Update active inputs styling
    const filledInputs = countFilledInputs();
    updateActiveInputs(filledInputs);
    
    // Auto-focus next input
    if (input.value.length === 1) {
      const nextIndex = parseInt(input.dataset.index) + 1;
      if (nextIndex < 5) {
        const nextInput = document.querySelector(`[data-index="${nextIndex}"]`);
        if (nextInput) nextInput.focus();
      } else {
        // If last input is filled, blur it to hide keyboard on mobile
        input.blur();
      }
    }
  }
  
  function handleKeyDown(event, input) {
    // Handle backspace to go to previous input
    if (event.key === 'Backspace' && input.value === '') {
      const prevIndex = parseInt(input.dataset.index) - 1;
      if (prevIndex >= 0) {
        const prevInput = document.querySelector(`[data-index="${prevIndex}"]`);
        if (prevInput) {
          prevInput.focus();
          // Optional: clear the previous input
          // prevInput.value = '';
        }
      }
    }
  }
  
  function handleFocus(input) {
    // Additional focus handling if needed beyond CSS
  }
  
  function handleBlur(input) {
    // Additional blur handling if needed beyond CSS
  }
  
  function updateHiddenField() {
    const inputs = document.querySelectorAll('[data-index]');
    let code = '';
    inputs.forEach(input => {
      code += input.value;
    });
    document.getElementById('quizcode-hidden').value = code;
  }
  
  function countFilledInputs() {
    const inputs = document.querySelectorAll('[data-index]');
    let count = 0;
    inputs.forEach(input => {
      if (input.value) {
        count++;
      }
    });
    return count;
  }
  
  function updateActiveInputs(activeCount) {
    const inputs = document.querySelectorAll('[data-index]');
    
    // Reset all inputs
    inputs.forEach(input => {
      input.classList.remove('border-primary', 'input-primary', 'scale-110', 'input-error', 'input-success', 'border-success');
    });
    
    // Apply styling to filled inputs
    for (let i = 0; i < activeCount; i++) {
      if (i < inputs.length) {
        inputs[i].classList.add('border-primary', 'input-primary', 'scale-110');
      }
    }
  }
  
  function checkQuizCode() {
    const code = document.getElementById('quizcode-hidden').value;
    
    if (code.length !== 5) {
      showError("Voer een 5-cijferige code in");
      return;
    }
    
    // Show checking status
    document.getElementById('code-status').classList.remove('hidden');
    
    // Make AJAX request to check if code exists
    fetch(`/quizzes/check-code/${code}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      // Hide checking status
      document.getElementById('code-status').classList.add('hidden');
      
      const inputs = document.querySelectorAll('[data-index]');
      
      if (data.valid) {
        document.getElementById('modal-quiz-title').textContent = data.title;
        
        // Apply success styling to inputs
        inputs.forEach(input => {
          input.classList.remove('input-error');
          input.classList.add('input-success', 'border-success');
        });
        
        showNameModal();
        
      } else {
        // Show error dialog
        showError("Code is incorrect. Controleer voor typefouten of probeer het opnieuw.");
        
        // Apply error styling to inputs
        inputs.forEach(input => {
          input.classList.remove('input-success', 'border-success');
          input.classList.add('input-error');
        });
      }
    })
    .catch(error => {
      console.error('Error checking quiz code:', error);
      document.getElementById('code-status').classList.add('hidden');
      showError("Er is een fout opgetreden bij het controleren van de code. Probeer het opnieuw.");
    });
  }

  function showNameModal() {
    document.getElementById('name-modal').showModal();
  }

  function closeNameModal() {
    document.getElementById('name-modal').close();
  }
  function showError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-modal').showModal();
  }

  function closeErrorModal() {
    document.getElementById('error-modal').close();
  }

  function submitName() {
    const playerName = document.getElementById('player-name-input').value.trim();
    if (playerName) {
      const inputs = document.querySelectorAll('[data-index]');
      const code = Array.from(inputs).map(input => input.value).join('');
      
      // Create form and submit
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/play/${code}`;
      
      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
      
      // Add name input
      const nameInput = document.createElement('input');
      nameInput.type = 'hidden';
      nameInput.name = 'name';
      nameInput.value = playerName;
      form.appendChild(nameInput);
      
      document.body.appendChild(form);
      document.getElementById('name-modal').close();
      form.submit();
    } else {
      showError("Voer je naam in");
    }
  }
</script>

@endsection
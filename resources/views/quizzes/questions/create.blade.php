@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
      <div class="flex justify-start mb-2">
          <a href="{{ route('quizzes.questions.index', $quiz->id) }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i>
          </a>
        </div>
        <div class="divider"></div>
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Voeg een vraag toe:</h2>
        <div class="flex flex-col gap-4">
          <form method="POST" action="{{ route('quizzes.questions.store', $quiz->id) }}" class="space-y-4" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="form-control">
              <label class="label">
                <span class="label-text">Vraag:</span>
              </label>
              <input type="text" name="question" class="input input-bordered" required autocomplete="off" />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Type vraag:</span>
              </label>
              <select name="type" class="select select-bordered" id="question-type" onchange="toggleQuestionOptions()" autocomplete="off">
                <option value="multiplechoice">Multiple Choice</option>
                <option value="truefalse">Waar/Niet waar</option>
              </select>
            </div>

            <div id="multiplechoice-options">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Antwoord opties (één per regel):</span>
                </label>
                <textarea name="options" class="textarea textarea-bordered" rows="4" autocomplete="off"></textarea>
              </div>

              <div class="form-control">
                <label class="label">
                  <span class="label-text">Juiste antwoord:</span>
                </label>
                <input type="text" name="correct_option" class="input input-bordered" autocomplete="off" />
              </div>
            </div>

            <div id="truefalse-options" class="hidden">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Juiste antwoord:</span>
                </label>
                <select name="tf_correct_option" class="select select-bordered" autocomplete="off">
                  <option value="true">Waar</option>
                  <option value="false">Niet waar</option>
                </select>
              </div>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Uitleg (optioneel):</span>
              </label>
              <textarea name="explanation" class="textarea textarea-bordered" autocomplete="off"></textarea>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Afbeelding (optioneel):</span>
              </label>
              <div class="border-2 border-dashed border-secondary rounded-lg p-6 text-center cursor-pointer" 
                   onclick="document.getElementById('image-upload').click()">
                <input type="file" id="image-upload" name="image" accept="image/*" class="hidden" onchange="previewImage(this)" autocomplete="off" />
                <a href="#" class="text-secondary hover:text-secondary-focus">Klik hier om een afbeelding te uploaden</a>
                <div id="image-preview" class="mt-4 hidden">
                  <img src="" alt="Preview" class="max-w-sm mx-auto rounded-lg shadow-lg" />
                </div>
              </div>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Tijd (seconden):</span>
              </label>
              <input type="number" name="time" class="input input-bordered" value="15" autocomplete="off" />
            </div>

            <div class="form-control mt-6">
              <button class="btn btn-primary">Vraag toevoegen</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')

<script>
function toggleQuestionOptions() {
  const questionType = document.getElementById('question-type').value;
  const mcOptions = document.getElementById('multiplechoice-options');
  const tfOptions = document.getElementById('truefalse-options');
  
  if (questionType === 'multiplechoice') {
    mcOptions.classList.remove('hidden');
    tfOptions.classList.add('hidden');
  } else if (questionType === 'truefalse') {
    mcOptions.classList.add('hidden');
    tfOptions.classList.remove('hidden');
  }
}

function previewImage(input) {
  const preview = document.getElementById('image-preview');
  const previewImg = preview.querySelector('img');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      preview.classList.remove('hidden');
    }
    
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.classList.add('hidden');
    previewImg.src = '';
  }
}

// Initialize the form
document.addEventListener('DOMContentLoaded', function() {
  toggleQuestionOptions();
});
</script>

@endsection
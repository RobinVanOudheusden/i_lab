@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
        <div class="flex justify-start mb-2">
          <a href="{{ route('quizzes.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i>
          </a>
        </div>
        <div class="divider"></div>
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Quiz bewerken:</h2>
        <div class="flex flex-col gap-4">
          <form method="POST" action="{{ route('quizzes.update', $quiz->id) }}" class="space-y-4" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')
            <div class="form-control">
              <label class="label">
                <span class="label-text">Titel:</span>
              </label>
              <input type="text" name="title" class="input input-bordered" value="{{ $quiz->title }}" required autocomplete="off" />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Beschrijving:</span>
              </label>
              <textarea name="description" class="textarea textarea-bordered" required autocomplete="off">{{ $quiz->description }}</textarea>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Afbeelding:</span>
              </label>
              <div class="border-2 border-dashed border-info rounded-lg p-6 text-center cursor-pointer" 
                   onclick="document.getElementById('image-upload').click()">
                <input type="file" id="image-upload" name="image" accept="image/*" class="hidden" onchange="previewImage(this)" autocomplete="off" />
                <a href="#" class="text-info hover:text-info-focus">Klik hier om een afbeelding te uploaden</a>
                <div id="image-preview" class="mt-4 {{ $quiz->image ? '' : 'hidden' }}">
                  <img src="{{ $quiz->image ?? '' }}" alt="Preview" class="max-w-sm mx-auto rounded-lg shadow-lg" />
                </div>
              </div>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Tags (komma-gescheiden):</span>
              </label>
              <input type="text" name="tags" class="input input-bordered" value="{{ $quiz->tags }}" onkeyup="createTags(this.value)" autocomplete="off" />
              <div id="tags-container" class="flex flex-wrap gap-2 mt-2"></div>
            </div>

            <div class="form-control mt-6">
              <button class="btn btn-info">Quiz bijwerken</button>
            </div>

            <div class="form-control mt-6">
              <a href="{{ route('quizzes.questions.index', $quiz->id) }}" class="btn btn-secondary">
                <i class="fas fa-eye mr-2"></i>
                Vragen bekijken/toevoegen
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')

<script>
function createTags(input) {
  const container = document.getElementById('tags-container');
  container.innerHTML = '';
  
  if(input) {
    const tags = input.split(',');
    tags.forEach(tag => {
      if(tag.trim() !== '') {
        const tagElement = document.createElement('div');
        tagElement.className = 'badge badge-info';
        tagElement.textContent = tag.trim();
        container.appendChild(tagElement);
      }
    });
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
      
      // If there's a remove image checkbox, uncheck it when a new image is selected
      const removeCheckbox = document.getElementById('remove_image');
      if (removeCheckbox) {
        removeCheckbox.checked = false;
      }
    }
    
    reader.readAsDataURL(input.files[0]);
  } else if (!document.getElementById('remove_image')?.checked) {
    // Only hide if we're not explicitly removing the image
    preview.classList.add('hidden');
    previewImg.src = '';
  }
}

// Initialize tags
createTags('{{ $quiz->tags }}');

// Handle remove image checkbox
const removeCheckbox = document.getElementById('remove_image');
if (removeCheckbox) {
  removeCheckbox.addEventListener('change', function() {
    const preview = document.getElementById('image-preview');
    if (this.checked) {
      preview.classList.add('hidden');
    } else {
      const imageInput = document.getElementById('image-upload');
      if (imageInput.files.length > 0 || '{{ $quiz->image }}') {
        preview.classList.remove('hidden');
      }
    }
  });
}
</script>

@endsection
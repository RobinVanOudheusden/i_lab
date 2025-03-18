@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">

    <div class="card bg-base-100 shadow-xl w-full max-w-md">
      <div class="card-body">
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Registreren</h2>
        
        <form method="POST" action="{{ route('register') }}" class="space-y-4" id="register-form" onsubmit="return validateForm()">
          @csrf
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Naam</span>
            </label>
            <input type="text" name="name" class="input input-bordered @error('name') input-error @enderror" value="{{ old('name') }}" required autofocus />
            @error('name')
              <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </label>
            @enderror
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text">E-mailadres</span>
            </label>
            <input type="email" name="email" class="input input-bordered @error('email') input-error @enderror" value="{{ old('email') }}" required />
            @error('email')
              <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </label>
            @enderror
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Wachtwoord</span>
            </label>
            <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror" required />
            @error('password')
              <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </label>
            @enderror
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text">Bevestig wachtwoord</span>
            </label>
            <input type="password" name="password_confirmation" class="input input-bordered" required />
          </div>

          <div class="form-control mt-4">
            <div class="g-recaptcha flex justify-center" data-sitekey="6LelPvYqAAAAAAao2ndbxu2zD8gNarwnF3ccRZYD"></div>
            <div id="recaptcha-error" class="text-error text-sm mt-2 text-center hidden">
              Vul de reCAPTCHA in om door te gaan.
            </div>
          </div>

          <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary">Registreren</button>
          </div>
          
          @if (Route::has('login'))
            <div class="text-center mt-4">
              <span>Al een account?</span>
              <a href="{{ route('login') }}" class="link link-hover text-secondary ml-1">
                Inloggen
              </a>
            </div>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')

<script>
function validateForm() {
  const response = grecaptcha.getResponse();
  const errorDiv = document.getElementById('recaptcha-error');
  
  if (!response) {
    errorDiv.classList.remove('hidden');
    return false;
  }
  
  errorDiv.classList.add('hidden');
  return true;
}
</script>

@endsection

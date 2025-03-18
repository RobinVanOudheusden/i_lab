@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">

    <div class="card bg-base-100 shadow-xl w-full max-w-md">
      <div class="card-body">
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Inloggen</h2>
        
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
          @csrf
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">E-mailadres</span>
            </label>
            <input type="email" name="email" class="input input-bordered @error('email') input-error @enderror" value="{{ old('email') }}" required autofocus />
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
          
          <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary">Inloggen</button>
          </div>
          
          @if (Route::has('password.request'))
            <div class="text-center mt-4">
              <a href="{{ route('password.request') }}" class="link link-hover text-secondary">
                Wachtwoord vergeten?
              </a>
            </div>
          @endif
          
          @if (Route::has('register'))
            <div class="text-center mt-2">
              <span>Nog geen account?</span>
              <a href="{{ route('register') }}" class="link link-hover text-secondary ml-1">
                Registreer
              </a>
            </div>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')

@endsection
@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">

    <div class="card border-2 border-secondary bg-base-100 shadow-xl w-full max-w-md">
      <div class="card-body"></div>
      <div class="flex flex-col items-center justify-center p-6 text-center">
        <h2 class="text-2xl font-bold mb-4">Wachten op de quizmaster</h2>
        <p class="mb-6">De quiz begint zodra de quizmaster deze start.</p>
        <div class="flex flex-col items-center gap-4">
          <span class="loading loading-spinner loading-lg text-primary"></span>
          <p class="text-base-content/70 text-sm">Even geduld a.u.b...</p>
        </div>
      </div>
    </div>
  </div>
</div>

@include('layouts.footer')

@endsection
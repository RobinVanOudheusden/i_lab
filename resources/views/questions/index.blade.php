@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body">
        <h2 class="card-title text-2xl sm:text-3xl md:text-4xl mb-6">Voorbeelden</h2>
        <div class="flex flex-col gap-4">
          <a href="/truefalse" class="btn btn-primary btn-lg h-16 text-xl">True/False Voorbeeld</a>
          <a href="/multiplechoice" class="btn btn-primary btn-lg h-16 text-xl">Multiple Choice Voorbeeld</a>
        </div>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')


@endsection
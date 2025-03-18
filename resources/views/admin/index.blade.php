@extends('layouts.app')
@include('layouts.header')
@section('content')

<div class="hero bg-base-200 min-h-screen">
  <div class="hero-content flex-col w-full max-w-4xl px-4 sm:px-6">
    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body p-4 sm:p-6">
        <h2 class="card-title text-xl sm:text-2xl md:text-3xl mb-4 sm:mb-6">Gebruikersbeheer</h2>
        
        @if(session('error'))
          <div class="alert alert-error mb-4">
            {{ session('error') }}
          </div>
        @endif
        
        @if(session('success'))
          <div class="alert alert-success mb-4">
            {{ session('success') }}
          </div>
        @endif
        
        <div class="mb-4">
          <div class="form-control">
            <div class="input-group relative">
              <input type="text" id="userSearch" placeholder="Zoeken..." class="input input-bordered w-full" />
              <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="clearSearch()">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        
        <div class="overflow-x-auto -mx-4 sm:mx-0">
          <table class="table table-zebra w-full text-sm sm:text-base">
            <thead>
              <tr>
                <th>Naam</th>
                <th class="hidden md:table-cell">E-mail</th>
                <th>Rol</th>
              </tr>
            </thead>
            <tbody id="userTableBody">
              @foreach($users as $user)
                @if($user->id !== auth()->user()->id)
                <tr class="user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-role="{{ strtolower($user->role) }}">
                  <td>
                    <div>{{ $user->name }}</div>
                    <div class="text-xs text-gray-500 md:hidden">{{ $user->email }}</div>
                  </td>
                  <td class="hidden md:table-cell">{{ $user->email }}</td>
                  <td>
                    <div 
                      class="text-sm font-medium cursor-pointer {{ $user->role === 'admin' ? 'text-primary' : ($user->role === 'teacher' ? 'text-accent' : 'text-info') }}"
                      onclick="document.getElementById('role-modal-{{ $user->id }}').showModal()"
                    >
                      {{ $user->role === 'admin' ? 'Beheerder' : ($user->role === 'teacher' ? 'Docent' : 'Student') }}
                    </div>
                    
                    <dialog id="role-modal-{{ $user->id }}" class="modal">
                      <div class="modal-box">
                        <h3 class="font-bold text-lg">Rol bijwerken voor {{ $user->name }}</h3>
                        <form action="{{ route('admin.update-role') }}" method="POST" class="mt-4">
                          @csrf
                          @method('PUT')
                          <input type="hidden" name="user" value="{{ $user->id }}">
                          <div class="form-control">
                            <label class="label">
                              <span class="label-text">Selecteer Rol</span>
                            </label>
                            <select name="role" class="select select-bordered w-full">
                              <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                              <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Docent</option>
                              <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Beheerder</option>
                            </select>
                          </div>
                          <div class="modal-action">
                            <button type="button" class="btn" onclick="document.getElementById('role-modal-{{ $user->id }}').close()">Annuleren</button>
                            <button type="submit" class="btn btn-primary">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                              </svg>
                              Bijwerken
                            </button>
                          </div>
                        </form>
                      </div>
                    </dialog>
                  </td>
                </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const userRows = document.querySelectorAll('.user-row');
    const clearButton = document.querySelector('.search-clear');
    
    searchInput.addEventListener('keyup', filterUsers);
    
    // Add event listener for the clear button (cross)
    if (clearButton) {
      clearButton.addEventListener('click', clearSearch);
    }
  });
  
  function filterUsers() {
    const searchInput = document.getElementById('userSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const userRows = document.querySelectorAll('.user-row');
    
    userRows.forEach(row => {
      const name = row.getAttribute('data-name');
      const email = row.getAttribute('data-email');
      const role = row.getAttribute('data-role');
      
      if (name.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }
  
  function clearSearch() {
    const searchInput = document.getElementById('userSearch');
    searchInput.value = '';
    
    // Reset all rows to visible
    const userRows = document.querySelectorAll('.user-row');
    userRows.forEach(row => {
      row.style.display = '';
    });
  }
</script>

@include('layouts.footer')


@endsection

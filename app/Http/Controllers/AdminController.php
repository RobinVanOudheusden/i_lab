<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('auth.join');
        }
        $users = \App\Models\User::all();
        return view('admin.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        // Validate the request
        $request->validate([
            'user' => 'required|exists:users,id',
            'role' => 'required|in:student,teacher,admin',
        ]);

        // Find the user to update
        $user = User::findOrFail($request->input('user'));
        
        // Update only the role
        $user->role = $request->input('role');
        $user->save();
        
        return redirect()->route('admin.index')->with('success', 'User role updated successfully');
    }

}

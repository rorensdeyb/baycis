<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        try {
            // Fetch all users to display in the management table
            $users = \App\Models\User::latest()->paginate(7);
            
            return view('admin.users', compact('users'));
        } catch (\Exception $e) {
            // Use our "Debug Shield" to see the real error if it still fails
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'teacher_id' => 'required|string|max:50|unique:users',
            'role' => 'required|in:admin,borrower',
        ]);

        // 2. Create the User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'teacher_id' => $request->teacher_id,
            'role' => $request->role,
            'password' => Hash::make('BayCIS2026!'), // The default password
            'is_active' => false, // Forces OTP verification on first login
            'requires_password_change' => true, // Good practice for later
        ]);

        // 3. Return success response to the frontend
        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully! They can now log in to verify their email.'
        ], 201);
    }
}
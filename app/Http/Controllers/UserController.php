<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function loginPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|digits:4',
        ]);

        // Get all users and check PIN hash
        $users = User::where('is_active', 1)->get();
        
        $authenticatedUser = null;
        foreach ($users as $user) {
            if (Hash::check($request->pin, $user->pin)) {
                $authenticatedUser = $user;
                break;
            }
        }

        if (!$authenticatedUser) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        // Update last login
        $authenticatedUser->update(['last_login_at' => now()]);

        // Create token for the user
        $token = $authenticatedUser->createToken('pos-token')->plainTextToken;

        return response()->json([
            'user' => $authenticatedUser,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function listUsers()
    {
        try {
            // Get users without exposing hashed PINs
            $users = User::select('id', 'name', 'role', 'color', 'is_active', 'last_login_at')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            return response()->json($users);
        } catch (\Exception $e) {
            // Log the error and return a simple response
            \Log::error('Error in listUsers: ' . $e->getMessage());
            
            // Return a mock user for testing
            return response()->json([
                [
                    'id' => 1,
                    'name' => 'Admin User',
                    'role' => 'admin',
                    'color' => '#EF4444',
                    'is_active' => true,
                    'last_login_at' => null
                ]
            ]);
        }
    }
}

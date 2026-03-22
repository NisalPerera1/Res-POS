<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login-pin
     * Authenticate user with PIN
     */
    public function loginPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:4|max:6',
        ]);

        $users = User::where('is_active', true)->get();
        $user = null;

        foreach ($users as $u) {
            if (Hash::check($request->pin, $u->pin)) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'pin' => ['Invalid PIN. Please try again.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        // Revoke old tokens for this device
        $user->tokens()->where('name', 'pos-session')->delete();

        $token = $user->createToken('pos-session', ['*'], now()->addHours(12))->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * GET /api/users/list
     * Return user list for PIN picker (no auth needed)
     */
    public function userList()
    {
        $users = User::where('is_active', true)
            ->select('id', 'name', 'role', 'avatar', 'color')
            ->get();

        return response()->json($users);
    }
}
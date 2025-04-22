<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiLoginController extends Controller
{

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $creadentials = $request->only('email', 'password') + ['status' => 'Active'];

        if (Auth::attempt($creadentials)) {
            $user = Auth::user();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login Done',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,

                ],
                'token' => $token,
            ], 200);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'role_name' => 'required|string|max:255',
            'password'  => 'required|string|min:8|confirmed',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'join_date' => $request->join_date,
            'last_login' => $request->last_login,
            'role_name' => $request->role_name,
            'status' => $request->status,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registered Successfully.',
            'details' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'join_date' => $user->join_date,
                'role_name' => $user->role_name,
                'status' => $user->status,
                'password' => $user->password,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        // $request->session()->flush();
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Logfed out successfully',

        ], 200);
    }
}

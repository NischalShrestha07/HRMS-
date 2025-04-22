<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\roleTypeUser;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /** Show the registration page */
    public function register()
    {
        $roles = roleTypeUser::get();
        return view('auth.register', compact('roles'));
    }

    /** Store a new user */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'role_name' => 'required|string|max:255',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        try {
            $todayDate = Carbon::now()->toDayDateTimeString();

            User::create([
                'name'      => $request->name,
                'avatar'    => $request->image,
                'email'     => $request->email,
                'join_date' => $todayDate,
                'last_login' => $todayDate,
                'role_name' => $request->role_name,
                'status'    => 'Active',
                'password'  => Hash::make($request->password),
            ]);

            flash()->success('Account created successfully :)');
            return redirect('login');
        } catch (\Exception $e) {
            \Log::error($e);
            flash()->error('Failed to create account. Please try again.');
            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'locked', 'unlock']);
    }

    /** Display the login page */
    public function login()
    {
        return view('auth.login');
    }

    /** Authenticate user and redirect */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $credentials = $request->only('email', 'password') + ['status' => 'Active'];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                Session::put($this->getUserSessionData($user));

                $user->update(['last_login' => Carbon::now()->toDayDateTimeString()]);

                flash()->success('Login successfully :)');
                return redirect()->intended('home');
            }

            flash()->error('Wrong Username or Password');
            return redirect('login');
        } catch (\Exception $e) {
            \Log::info($e);
            flash()->error('Login failed. Please try again.');
            return redirect()->back();
        }
    }

    /** Prepare user session data */
    private function getUserSessionData($user)
    {
        return [
            'name'         => $user->name,
            'email'        => $user->email,
            'user_id'      => $user->user_id,
            'join_date'    => $user->join_date,
            'phone_number' => $user->phone_number,
            'status'       => $user->status,
            'role_name'    => $user->role_name,
            'avatar'       => $user->avatar,
            'position'     => $user->position,
            'department'   => $user->department,
        ];
    }

    /** Logout and clear session */
    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        flash()->success('Logout successfully :)');
        return redirect('login');
    }
}

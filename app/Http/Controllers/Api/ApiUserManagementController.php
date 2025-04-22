<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\department;
use App\Models\positionType;
use App\Models\roleTypeUser;
use App\Models\User;
use App\Models\userType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiUserManagementController extends Controller
{
    public function users(Request $request)
    {
        $user = Auth::user();
        if ($user->role_name == 'Admin') {
            $result = User::get();
            $role_name = roleTypeUser::get();
            $position = positionType::get();
            $department = department::get();
            $status_user = userType::get();
        }
        return response()->json([
            'status'=>'success',
            'message'=>'User Details Reteived successfully.',
            'user_de'=>
        ])
    }
}

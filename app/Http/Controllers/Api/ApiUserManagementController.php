<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\department;
use App\Models\positionType;
use App\Models\roleTypeUser;
use App\Models\User;
use App\Models\userType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiUserManagementController extends Controller
{

    public function index(Request $request)
    {

        // Retrieve data from tables
        $users = DB::table('users')->get();
        $role_types = DB::table('role_type_users')->get();
        $position_types = DB::table('position_types')->get();
        $departments = DB::table('departments')->get();
        $user_statuses = DB::table('user_types')->get();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'User management data retrieved successfully',
            'data' => [
                'users' => $users,
                'role_types' => $role_types,
                'position_types' => $position_types,
                'departments' => $departments,
                'user_statuses' => $user_statuses,
            ],
        ], 200);
    }
}

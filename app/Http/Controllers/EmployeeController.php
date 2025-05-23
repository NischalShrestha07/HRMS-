<?php

namespace App\Http\Controllers;

use App\Models\module_permission;
use Illuminate\Http\Request;
use App\Models\department;
use App\Models\Employee;
use App\Models\permission_list;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /** All Employee Card View */
    public function cardAllEmployee(Request $request)
    {
        $users = User::join('employees', 'users.user_id', 'employees.employee_id')
            ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
            ->get();
        $userList = User::get();
        $permission_lists = permission_list::get();
        return view('employees.allemployeecard', compact('users', 'userList', 'permission_lists'));
    }


    /** All Employee List */
    public function listAllEmployee()
    {
        $users = User::join('employees', 'users.user_id', 'employees.employee_id')
            ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
            ->get();
        $userList = User::get();
        $permission_lists = permission_list::get();
        return view('employees.employeelist', compact('users', 'userList', 'permission_lists'));
    }

    /** Save Data Employee */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email',
            'birthDate'   => 'required|string|max:255',
            'gender'      => 'required|string|max:255',
            'employee_id' => 'required|string|max:255',
            'company'     => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {

            $employees = Employee::where('email', '=', $request->email)->first();
            if ($employees === null) {

                $employee = new Employee;
                $employee->name         = $request->name;
                $employee->email        = $request->email;
                $employee->birth_date   = $request->birthDate;
                $employee->gender       = $request->gender;
                $employee->employee_id  = $request->employee_id;
                $employee->company      = $request->company;
                $employee->save();

                for ($i = 0; $i < count($request->id_count); $i++) {
                    $module_permissions = [
                        'employee_id' => $request->employee_id,
                        'module_permission' => $request->permission[$i],
                        'id_count'          => $request->id_count[$i],
                        'read'              => $request->read[$i],
                        'write'             => $request->write[$i],
                        'create'            => $request->create[$i],
                        'delete'            => $request->delete[$i],
                        'import'            => $request->import[$i],
                        'export'            => $request->export[$i],
                    ];
                    module_permission::insert($module_permissions);
                }

                DB::commit();
                flash()->success('Add new employee successfully :)');
                return redirect()->route('all/employee/card');
            } else {
                DB::rollback();
                flash()->success('Add new employee exits :)');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Add new employee fail :)');
            return redirect()->back();
        }
    }

    /** Edit Record */
    public function viewRecord($employee_id)
    {
        $permission = Employee::join('module_permissions', 'employees.employee_id', 'module_permissions.employee_id')
            ->select('employees.*', 'module_permissions.*')->where('employees.employee_id', $employee_id)->get();
        $employees = Employee::where('employee_id', $employee_id)->get();
        return view('employees.edit.editemployee', compact('employees', 'permission'));
    }

    /** Update Record */
    public function updateRecord(Request $request)
    {
        DB::beginTransaction();
        try {

            // update table Employee
            $updateEmployee = [
                'id'          => $request->id,
                'name'        => $request->name,
                'email'       => $request->email,
                'birth_date'  => $request->birth_date,
                'gender'      => $request->gender,
                'employee_id' => $request->employee_id,
                'company'     => $request->company,
            ];

            // update table user
            $updateUser = [
                'id' => $request->id,
                'name' => $request->name,
                'email' => $request->email,
            ];

            // update table module_permissions
            for ($i = 0; $i < count($request->id_permission); $i++) {
                $UpdateModule_permissions = [
                    'employee_id' => $request->employee_id,
                    'module_permission' => $request->permission[$i],
                    'id'                => $request->id_permission[$i],
                    'read'              => $request->read[$i],
                    'write'             => $request->write[$i],
                    'create'            => $request->create[$i],
                    'delete'            => $request->delete[$i],
                    'import'            => $request->import[$i],
                    'export'            => $request->export[$i],
                ];
                module_permission::where('id', $request->id_permission[$i])->update($UpdateModule_permissions);
            }

            User::where('id', $request->id)->update($updateUser);
            Employee::where('id', $request->id)->update($updateEmployee);

            DB::commit();
            flash()->success('Updated record successfully :)');
            return redirect()->route('all/employee/card');
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Updated record fail :)');
            return redirect()->back();
        }
    }

    /** Delete Record */
    public function deleteRecord($employee_id)
    {
        DB::beginTransaction();
        try {
            Employee::where('employee_id', $employee_id)->delete();
            module_permission::where('employee_id', $employee_id)->delete();

            DB::commit();
            flash()->success('Delete record successfully :)');
            return redirect()->route('all/employee/card');
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Delete record fail :)');
            return redirect()->back();
        }
    }

    /** employee search */
    public function employeeSearch(Request $request)
    {
        $users = DB::table('users')
            ->join('employees', 'users.user_id', 'employees.employee_id')
            ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')->get();
        $permission_lists = permission_list::get();
        $userList = User::get();

        // search by id
        if ($request->employee_id) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')->get();
        }
        // search by name
        if ($request->name) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')->get();
        }
        // search by name
        if ($request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }

        // search by name and id
        if ($request->employee_id && $request->name) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')
                ->get();
        }
        // search by position and id
        if ($request->employee_id && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }
        // search by name and position
        if ($request->name && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }
        // search by name and position and id
        if ($request->employee_id && $request->name && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }
        return view('employees.allemployeecard', compact('users', 'userList', 'permission_lists'));
    }

    /** List Search */
    public function employeeListSearch(Request $request)
    {
        $users = User::join('employees', 'users.user_id', 'employees.employee_id')
            ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')->get();
        $permission_lists = permission_list::get();
        $userList         = User::get();

        // search by id
        if ($request->employee_id) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')->get();
        }

        // search by name
        if ($request->name) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')->get();
        }

        // search by name
        if ($request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }

        // search by name and id
        if ($request->employee_id && $request->name) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')->get();
        }

        // search by position and id
        if ($request->employee_id && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }

        // search by name and position
        if ($request->name && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }

        // search by name and position and id
        if ($request->employee_id && $request->name && $request->position) {
            $users = User::join('employees', 'users.user_id', 'employees.employee_id')
                ->select('users.*', 'employees.birth_date', 'employees.gender', 'employees.company')
                ->where('employee_id', 'LIKE', '%' . $request->employee_id . '%')
                ->where('users.name', 'LIKE', '%' . $request->name . '%')
                ->where('users.position', 'LIKE', '%' . $request->position . '%')->get();
        }
        return view('employees.employeelist', compact('users', 'userList', 'permission_lists'));
    }

    /** Employee profile */
    public function profileEmployee($user_id)
    {
        $user = User::leftJoin('personal_information as pi', 'pi.user_id', 'users.user_id')
            ->leftJoin('profile_information as pr', 'pr.user_id', 'users.user_id')
            ->leftJoin('user_emergency_contacts as ue', 'ue.user_id', 'users.user_id')
            ->select(
                'users.*',
                'pi.passport_no',
                'pi.passport_expiry_date',
                'pi.tel',
                'pi.nationality',
                'pi.religion',
                'pi.marital_status',
                'pi.employment_of_spouse',
                'pi.children',
                'pr.birth_date',
                'pr.gender',
                'pr.address',
                'pr.country',
                'pr.state',
                'pr.pin_code',
                'pr.phone_number',
                'pr.department',
                'pr.designation',
                'pr.reports_to',
                'ue.name_primary',
                'ue.relationship_primary',
                'ue.phone_primary',
                'ue.phone_2_primary',
                'ue.name_secondary',
                'ue.relationship_secondary',
                'ue.phone_secondary',
                'ue.phone_2_secondary'
            )
            ->where('users.user_id', $user_id)->get();
        $users = User::leftJoin('personal_information as pi', 'pi.user_id', 'users.user_id')
            ->leftJoin('profile_information as pr', 'pr.user_id', 'users.user_id')
            ->leftJoin('user_emergency_contacts as ue', 'ue.user_id', 'users.user_id')
            ->select(
                'users.*',
                'pi.passport_no',
                'pi.passport_expiry_date',
                'pi.tel',
                'pi.nationality',
                'pi.religion',
                'pi.marital_status',
                'pi.employment_of_spouse',
                'pi.children',
                'pr.birth_date',
                'pr.gender',
                'pr.address',
                'pr.country',
                'pr.state',
                'pr.pin_code',
                'pr.phone_number',
                'pr.department',
                'pr.designation',
                'pr.reports_to',
                'ue.name_primary',
                'ue.relationship_primary',
                'ue.phone_primary',
                'ue.phone_2_primary',
                'ue.name_secondary',
                'ue.relationship_secondary',
                'ue.phone_secondary',
                'ue.phone_2_secondary'
            )
            ->where('users.user_id', $user_id)->first();

        return view('employees.employeeprofile', compact('user', 'users'));
    }

    /** Page Departments */
    public function index()
    {
        $departments = department::get();
        return view('employees.departments', compact('departments'));
    }

    /** Save Record */
    public function saveRecordDepartment(Request $request)
    {
        $request->validate([
            'department' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {

            $department = department::where('department', $request->department)->first();
            if ($department === null) {
                $department = new department;
                $department->department = $request->department;
                $department->save();

                DB::commit();
                flash()->success('Add new department successfully :)');
                return redirect()->back();
            } else {
                DB::rollback();
                flash()->error('Add new department exits :)');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Add new department fail :)');
            return redirect()->back();
        }
    }

    /** Update Record */
    public function updateRecordDepartment(Request $request)
    {
        DB::beginTransaction();
        try {
            // update table departments
            $department = [
                'id'         => $request->id,
                'department' => $request->department,
            ];
            department::where('id', $request->id)->update($department);
            DB::commit();
            flash()->success('Updated record successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash()->success('Updated record fail :)');
            return redirect()->back();
        }
    }

    /** Delete Record */
    public function deleteRecordDepartment(Request $request)
    {
        try {
            department::destroy($request->id);
            flash()->success('Department deleted successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Department delete fail :)');
            return redirect()->back();
        }
    }

    /** Page Designations */
    public function designationsIndex()
    {
        return view('employees.designations');
    }

    /** Page Time Sheet */
    public function timeSheetIndex()
    {
        return view('employees.timesheet');
    }

    /** Page Overtime */
    public function overTimeIndex()
    {
        return view('employees.overtime');
    }
}

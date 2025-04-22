<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeavesAdmin;
use DateTime;
use DB;

class LeavesController extends Controller
{
    /** leaves page */
    public function leaves()
    {
        $leaves = LeavesAdmin::join('users', 'users.user_id', 'leaves_admins.user_id')->select('leaves_admins.*', 'users.position', 'users.name', 'users.avatar')->get();
        return view('employees.leaves', compact('leaves'));
    }

    /** Save Record */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'leave_type'   => 'required|string|max:255',
            'from_date'    => 'required|string|max:255',
            'to_date'      => 'required|string|max:255',
            'leave_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {

            $from_date = new DateTime($request->from_date);
            $to_date   = new DateTime($request->to_date);
            $day       = $from_date->diff($to_date);
            $days      = $day->d;

            $leaves = new LeavesAdmin;
            $leaves->user_id       = $request->user_id;
            $leaves->leave_type    = $request->leave_type;
            $leaves->from_date     = $request->from_date;
            $leaves->to_date       = $request->to_date;
            $leaves->day           = $days;
            $leaves->leave_reason  = $request->leave_reason;
            $leaves->save();

            DB::commit();
            flash()->success('Create new Leaves successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Add Leaves fail :)');
            return redirect()->back();
        }
    }

    /** Edit Record */
    public function editRecordLeave(Request $request)
    {
        DB::beginTransaction();
        try {

            $from_date = new DateTime($request->from_date);
            $to_date   = new DateTime($request->to_date);
            $day       = $from_date->diff($to_date);
            $days      = $day->d;

            $update = [
                'id'           => $request->id,
                'leave_type'   => $request->leave_type,
                'from_date'    => $request->from_date,
                'to_date'      => $request->to_date,
                'day'          => $days,
                'leave_reason' => $request->leave_reason,
            ];

            LeavesAdmin::where('id', $request->id)->update($update);
            DB::commit();
            flash()->success('Updated Leaves successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Update Leaves fail :)');
            return redirect()->back();
        }
    }

    /** Delete Record */
    public function deleteLeave(Request $request)
    {
        try {
            LeavesAdmin::destroy($request->id);
            flash()->success('Leaves admin deleted successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Leaves admin delete fail :)');
            return redirect()->back();
        }
    }

    /** Leave Settings Page */
    public function leaveSettings()
    {
        return view('employees.leavesettings');
    }

    /** Attendance Admin */
    public function attendanceIndex()
    {
        return view('employees.attendance');
    }

    /** Attendance Employee */
    public function AttendanceEmployee()
    {
        return view('employees.attendanceemployee');
    }

    /** Leaves Employee */
    public function leavesEmployee()
    {
        return view('employees.leavesemployee');
    }

    /** Shift Scheduling */
    public function shiftScheduLing()
    {
        return view('employees.shiftscheduling');
    }

    /** Shift List */
    public function shiftList()
    {
        return view('employees.shiftlist');
    }
}

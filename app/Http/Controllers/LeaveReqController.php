<?php

namespace App\Http\Controllers;

use App\Models\LeaveReq;
use App\Http\Requests\StoreLeaveReqRequest;
use App\Http\Requests\UpdateLeaveReqRequest;
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\LeaveReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class LeaveReqController extends Controller
{   

    public function getexecutivesec() 
    {
        $leaveRequests = LeaveReq::with(['departmentHead', 'user', 'leaveType'])
            ->where('dept_head', 'Approved') // Filter for approved requests
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($leaveRequests);
    }

    public function getbypresident() 
    {
        $leaveRequests = LeaveReq::with(['departmentHead', 'user', 'leaveType'])
            ->where('exec_sec', 'Approved') // Filter for approved requests
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($leaveRequests);
    }



    public function getByDHead($dheadId)
    {
        $leaveRequests = LeaveReq::with(['departmentHead', 'user','leaveType']) // Eager load user and department head
            ->where('DHead', $dheadId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($leaveRequests);
    }


    public function getDepartmentHeads()
    {
        // Query the users table to get the department heads
        $departmentHeads = User::where(function($query) {
            // For Administrators
            $query->where('department', 'Administrators')
                  ->where('position', 'Treasurer');
        })
        ->orWhere(function($query) {
            // For Directors
            $query->where('department', 'Directors')
                  ->where(function($query) {
                      $query->where('position', 'LIKE', '%Ministerial%')
                            ->orWhere('position', 'LIKE', '%Education%')
                            ->orWhere('position', 'LIKE', '%Communication%')
                            ->orWhere('position', 'LIKE', '%Spirit of Prophecy%');
                  });
        })
        ->select('id', 'name')
        ->get();

        // Return the results (you can return as JSON or pass to a view)
        return response()->json($departmentHeads);
    }
    public function countLeaveAndEvents($userid)
{
    $today = now()->toDateString(); // Get today's date (YYYY-MM-DD)

    // Count leave requests by status
    $leaveCounts = DB::table('leave_reqs')
        ->selectRaw("
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
        ")
        ->where('userid', $userid)
        ->first();

    // Count events happening today
    $eventCount = DB::table('events')
        ->where('userid', $userid)
        ->whereDate('time', $today)
        ->count();

    return response()->json([
        'pending'  => $leaveCounts->pending ?? 0,
        'approved' => $leaveCounts->approved ?? 0,
        'rejected' => $leaveCounts->rejected ?? 0,
        'events_today' => $eventCount
    ]);
}

//     public function countLeaveRequests($userid)
//     {
//         $counts = LeaveReq::where('userid', $userid)
//             ->selectRaw("status, COUNT(*) as count")
//             ->groupBy('status')
//             ->pluck('count', 'status');

//         return response()->json([
//             'Pending' => $counts['Pending'] ?? 0,
//             'Approved' => $counts['Approved'] ?? 0,
//             'Rejected' => $counts['Rejected'] ?? 0,
//         ]);
// }
    // Get all leave requests
    public function index()
    {
        return response()->json(LeaveReq::with(['user', 'leaveType'])->get());
    }
    public function store(Request $request)
{
    $request->validate([
        'userid' => 'required|exists:users,id',
        'leavetypeid' => 'required|exists:leavetypes,id',
        'from' => 'required|date',
        'to' => 'required|date',
        'reason' => 'nullable|string|max:1000',
        'DHead' => 'nullable|string|max:1000'
    ]);

    $user = DB::table('users')->where('id', $request->userid)->first();

    // Determine the requester's role
    if ($user->department === 'Administrators' && $user->position === 'President') {
        $dept_head_status = 'Approved';
        $exec_sec_status = 'Approved';
        $president_status = 'Approved';
    } elseif ($user->department === 'Administrators' && stripos($user->position, 'Executive Secretary') !== false) {
        $dept_head_status = 'Approved';
        $exec_sec_status = 'Approved';
        $president_status = 'Pending';
    }
     elseif (
        $user->department === 'Directors' || 
        ($user->department === 'Administrators' && stripos($user->position, 'Treasurer') !== false)
    ) { 
        $dept_head_status = 'Approved';
        $exec_sec_status = 'Pending';
        $president_status = 'Pending';
    }
    
     else {
        $dept_head_status = 'Pending';
        $exec_sec_status = 'Pending';
        $president_status = 'Pending';
    }

    Log::info("Creating leave request for user ID: {$request->userid}");

    // Create Leave Request
    $leaveReq = LeaveReq::create([
        'userid' => $request->userid,
        'leavetypeid' => $request->leavetypeid,
        'from' => $request->from,
        'to' => $request->to,
        'reason' => $request->reason,
        'DHead' => $request->DHead, 
        'dept_head' => $dept_head_status,
        'exec_sec' => $exec_sec_status,
        'president' => $president_status
    ]);

    Log::info("Leave request created with ID: {$leaveReq->id}");

    // Send Notifications
    $notifications = [];
    
    // Notify Department Head if status is "Pending"
    if ($leaveReq->dept_head === 'Pending') { 
        // Use the DHead column from leave_reqs as the userid for notifications
        $departmentHead = User::where('id', $leaveReq->DHead)->first();
    
        if ($departmentHead) {
            $notifications[] = [
                'userid' => $departmentHead->id, // Assign the DHead user ID
                'message' => "{$user->name} has submitted a leave request, pending your approval.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            Log::info("Notification prepared for Department Head ID: {$departmentHead->id}");
        }
    }
    // Notify Executive Secretary if Dept Head approved but Exec Sec is still pending
    if ($leaveReq->DHead === NULL && $leaveReq->dept_head === 'Approved' && $leaveReq->exec_sec === 'Pending' && $leaveReq->president === 'Pending') {
        $executiveSecretary = User::where('department', 'Administrators')
            ->where('position', 'Executive Secretary')
            ->first();
    
        if ($executiveSecretary) {
            $notifications[] = [
                'userid' => $executiveSecretary->id,
                'message' => "{$user->name} has submitted a leave request, pending your approval.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            Log::info("Notification prepared for Executive Secretary ID: {$executiveSecretary->id}");
        }
    }

    // Notify President if both Dept Head & Exec Sec approved but President is still pending
    if ($leaveReq->dept_head === 'Approved' && $leaveReq->exec_sec === 'Approved' && $leaveReq->president === 'Pending') {
        $president = User::where('position', 'President')->first();

        if ($president) {
            $notifications[] = [
                'userid' => $president->id,
                'message' => "{$user->name} has submitted a leave request, pending your approval.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            Log::info("Notification prepared for President ID: {$president->id}");
        }
    } 
    if ($user->department === 'Directors' && $leaveReq->exec_sec === 'Pending') {
        $executiveSecretary = User::where('department', 'Administrators')
        ->where('position', 'like', '%Executive Secretary%')
        ->first();
        Log::info('Checking for Directors -> Exec Sec notification');

        if ($executiveSecretary) {
            DB::table('notifications')->insert([
                'userid' => $executiveSecretary->id,
                'message' => "{$user->name} has submitted a leave request.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info('Executive Secretary notified from Directors department');
        } else {
            Log::warning('Executive Secretary not found for Directors');
        }
    }
    
    // Insert all notifications at once
    if (!empty($notifications)) {
        DB::table('notifications')->insert($notifications);
        Log::info("Notifications inserted into database for leave request ID: {$leaveReq->id}");
    }

    return response()->json($leaveReq, 201);
}

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'userid' => 'required|exists:users,id',
    //         'leavetypeid' => 'required|exists:leavetypes,id',
    //         'from' => 'required|date',
    //         'to' => 'required|date',
    //         'reason' => 'nullable|string|max:1000',
    //         'DHead' => 'nullable|string|max:255', // Department Head name input
    //     ]);

    //     $user = DB::table('users')->where('id', $request->userid)->first();

    //     // Find the Department Head's user ID based on department
    //     $departmentHead = DB::table('users')
    //         ->where('name', $request->DHead)
    //         ->where('department', 'Directors')
    //         ->first();

    //     // Find the Executive Secretary
    //     // $executiveSecretary = DB::table('users')
    //     //     ->where('department', 'Administrators')
    //     //     ->where('position', 'Executive Secretary')
    //     //     ->first();

    //     // Create Leave Request
    //     $leaveReq = LeaveReq::create([
    //         'userid' => $request->userid,
    //         'leavetypeid' => $request->leavetypeid,
    //         'from' => $request->from,
    //         'to' => $request->to,
    //         'reason' => $request->reason,
    //         'DHead' => $request->DHead, // Store department name
    //         'dept_head' => 'Pending',
    //         'exec_sec' => 'Pending',
    //         'president' => 'Pending'
    //     ]);

    //     // Notify the Department Head
    //     if ($departmentHead) {
    //         DB::table('notifications')->insert([
    //             'userid' => $departmentHead->id, // Notify Department Head
    //             'message' => "{$user->name} has submitted a leave request, pending your approval.",
    //             'type' => 'Leave Request',
    //             'is_read' => 0,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     // Notify the Executive Secretary
    //     // if ($executiveSecretary) {
    //     //     DB::table('notifications')->insert([
    //     //         'userid' => $executiveSecretary->id, // Notify Executive Secretary
    //     //         'message' => "{$user->name} has submitted a leave request for {$request->DHead}, pending approval.",
    //     //         'type' => 'Leave Request',
    //     //         'is_read' => 0,
    //     //         'created_at' => now(),
    //     //         'updated_at' => now(),
    //     //     ]);
    //     // }

    //     return response()->json($leaveReq, 201);
    // }

    // Get single leave request
   
    public function showByUserId($userid)
    {
        $leaveRequests = LeaveReq::with(['user', 'leaveType'])
        ->where('userid', $userid)
        ->orderBy('created_at', 'desc')
        ->get();

        if ($leaveRequests->isEmpty()) {
            return response()->json(['message' => 'No leave requests found for this user.'], 404);
        }

        return response()->json($leaveRequests);
    }

    
    // Update leave request
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected'
        ]);

        $leaveReq = LeaveReq::findOrFail($id);
        $leaveReq->update($request->only('status'));

        return response()->json($leaveReq);
    }

    // Update leave request without requiring status
    public function updateDetails(Request $request, $id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Validate inputs
        $request->validate([
            'userid' => 'sometimes|integer',
            'leavetypeid' => 'sometimes|integer',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date',
            'reason' => 'sometimes|string',
        ]);

        $leaveReq->update([
            'userid' => $request->input('userid') ?? $leaveReq->userid,
            'leavetypeid' => $request->input('leavetypeid') ?? $leaveReq->leavetypeid,
            'from' => $request->input('from') ?? $leaveReq->from,
            'to' => $request->input('to') ?? $leaveReq->to,
            'reason' => $request->input('reason') ?? $leaveReq->reason,
        ]);

        return response()->json(['message' => 'Leave request details updated successfully'], 200);
    }
    
    public function approveLeaveRequest($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the dept_head is already approved
        if ($leaveReq->dept_head === 'Approved') {
            return response()->json(['message' => 'Leave request is already approved'], 400);
        }

        // Update the leave request dept_head to Approved
        $leaveReq->update([
            'dept_head' => 'Approved',
        ]);

        // Fetch the user's name who requested the leave
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();

        // Send a notification to the user
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "Department Head has approved your leave request.",
                'type' => 'Leave Approval',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // notify exec sec 
        $executiveSecretaries = DB::table('users')
        ->where('department', 'Administrators')
        ->whereRaw("position LIKE ?", ['%Executive Secretary%'])
        ->get();

        foreach ($executiveSecretaries as $secretary) {
            DB::table('notifications')->insert([
                'userid' => $secretary->id,
                'message' => "{$user->name}'s Department Head has approved a leave request, pending your approval.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        return response()->json(['message' => 'Leave request approved successfully'], 200);
    }



    
    public function rejectLeaveRequest($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the dept_head is already Rejected
        if ($leaveReq->dept_head === 'Rejected') {
            return response()->json(['message' => 'Leave request is already Rejected'], 400);
        }

        $leaveReq->update([
            'dept_head' => 'Rejected',
        ]);
        
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "Department Head has rejected your leave request.",
                'type' => 'Leave Rejected',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return response()->json(['message' => 'Leave request Rejected successfully'], 200);
    }

    // exec 

    public function approveLeaveRequestexecsec($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the exec_sec is already approved
        if ($leaveReq->exec_sec === 'Approved') {
            return response()->json(['message' => 'Leave request is already approved'], 400);
        }

        // Update the leave request exec_sec to Approved
        $leaveReq->update([
            'exec_sec' => 'Approved',
        ]);

        // Fetch the user's name who requested the leave
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();

        // Send a notification to the user
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "Executive Secretary has approved your leave request.",
                'type' => 'Leave Approval',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $executiveSecretaries = DB::table('users')
        ->where('department', 'Administrators')
        ->whereRaw("position LIKE ?", ['%President%'])
        ->get();

        foreach ($executiveSecretaries as $secretary) {
            DB::table('notifications')->insert([
                'userid' => $secretary->id,
                'message' => "{$user->name} | Department Head & Executive Secretary has approved a leave request, pending your approval.",
                'type' => 'Leave Request',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Leave request approved successfully'], 200);
    }
    public function rejectLeaveRequestexecsec($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the exec_sec is already Rejected
        if ($leaveReq->exec_sec === 'Rejected') {
            return response()->json(['message' => 'Leave request is already Rejected'], 400);
        }

        $leaveReq->update([
            'exec_sec' => 'Rejected',
        ]);
        
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "Executive Secretary has rejected your leave request.",
                'type' => 'Leave Rejected',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return response()->json(['message' => 'Leave request Rejected successfully'], 200);
    }

    // pres 

    public function approveLeaveRequestpres($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the president is already approved
        if ($leaveReq->president === 'Approved') {
            return response()->json(['message' => 'Leave request is already approved'], 400);
        }

        // Update the leave request president to Approved
        $leaveReq->update([
            'president' => 'Approved',
        ]);

        // Fetch the user's name who requested the leave
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();

        // Send a notification to the user
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "President has approved your leave request.",
                'type' => 'Leave Approval',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Leave request approved successfully'], 200);
    }
    public function rejectLeaveRequestpres($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the president is already Rejected
        if ($leaveReq->president === 'Rejected') {
            return response()->json(['message' => 'Leave request is already Rejected'], 400);
        }

        $leaveReq->update([
            'president' => 'Rejected',
        ]);
        
        $user = DB::table('users')->where('id', $leaveReq->userid)->first();
        if ($user) {
            DB::table('notifications')->insert([
                'userid' => $leaveReq->userid, // Notify the user who requested leave
                'message' => "President has rejected your leave request.",
                'type' => 'Leave Rejected',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return response()->json(['message' => 'Leave request Rejected successfully'], 200);
    }
    
    // Delete leave request
    public function destroy($id)
    {
        LeaveReq::findOrFail($id)->delete();
        return response()->json(['message' => 'Leave request deleted']);
    }
}


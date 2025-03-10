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
class LeaveReqController extends Controller
{   
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
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'reason' => 'nullable|string|max:1000',
        ]);
    
        $leaveReq = LeaveReq::create([
            'userid' => $request->userid,
            'leavetypeid' => $request->leavetypeid,
            'from' => $request->from,
            'to' => $request->to,
            'reason' => $request->reason,
            'status' => 'Pending' // Automatically set status to Pending
        ]);
    
        return response()->json($leaveReq, 201);
    }
    


    // Get single leave request
   
    public function showByUserId($userid)
    {
        $leaveRequests = LeaveReq::with(['user', 'leaveType'])->where('userid', $userid)->get();

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

        // Check if the status is already approved
        if ($leaveReq->status === 'Approved') {
            return response()->json(['message' => 'Leave request is already approved'], 400);
        }

        $leaveReq->update([
            'status' => 'Approved',
        ]);

        return response()->json(['message' => 'Leave request approved successfully'], 200);
    }

    
    public function rejectLeaveRequest($id)
    {
        $leaveReq = LeaveReq::find($id);

        if (!$leaveReq) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Check if the status is already Rejected
        if ($leaveReq->status === 'Rejected') {
            return response()->json(['message' => 'Leave request is already Rejected'], 400);
        }

        $leaveReq->update([
            'status' => 'Rejected',
        ]);

        return response()->json(['message' => 'Leave request Rejected successfully'], 200);
    }
    
    // Delete leave request
    public function destroy($id)
    {
        LeaveReq::findOrFail($id)->delete();
        return response()->json(['message' => 'Leave request deleted']);
    }
}


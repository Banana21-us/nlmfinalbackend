<?php

namespace App\Http\Controllers;

use App\Models\LeaveReq;
use App\Http\Requests\StoreLeaveReqRequest;
use App\Http\Requests\UpdateLeaveReqRequest;
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\LeaveReq;
use Illuminate\Http\Request;

class LeaveReqController extends Controller
{
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
    
    // Delete leave request
    public function destroy($id)
    {
        LeaveReq::findOrFail($id)->delete();
        return response()->json(['message' => 'Leave request deleted']);
    }
}


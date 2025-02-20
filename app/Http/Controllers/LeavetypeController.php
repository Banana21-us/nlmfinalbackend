<?php

namespace App\Http\Controllers;

use App\Models\leavetype;
use App\Http\Requests\StoreleavetypeRequest;
use App\Http\Requests\UpdateleavetypeRequest;
use Illuminate\Http\Request;
class LeavetypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all leave types from the database
        $leaveTypes = LeaveType::all();
        return response()->json($leaveTypes); // Returning as JSON for API or you can return a view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate and create a new leave type
        $leaveType = LeaveType::create([
            'type' => $request->type,
            'days_allowed' => $request->days_allowed,
            'description' => $request->description,
        ]);

        // Return the created leave type
        return response()->json($leaveType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        // Return the specific leave type by its ID
        return response()->json($leaveType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        // Validate and update the leave type
        $leaveType->update([
            'type' => $request->type,
            'days_allowed' => $request->days_allowed,
            'description' => $request->description,
        ]);

        // Return the updated leave type
        return response()->json($leaveType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Delete the leave type from the database
        $leaveType->delete();

        // Return a success response
        return response()->json(['message' => 'Leave type deleted successfully']);
    }
}

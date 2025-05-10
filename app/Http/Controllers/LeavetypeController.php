<?php

namespace App\Http\Controllers;

use App\Models\leavetype;
use App\Http\Requests\StoreleavetypeRequest;
use App\Http\Requests\UpdateleavetypeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveReq;
class LeavetypeController extends Controller
{   
    
    public function getLeaveBalances($userId)
    {
        $user = User::findOrFail($userId);
    
        if (!$user->reg_approval) {
            return response()->json(['error' => 'Registration approval date not set'], 400);
        }
    
        $yearsOfService = Carbon::parse($user->reg_approval)->diffInYears(Carbon::now());
    
        if ($yearsOfService >= 16) {
            $vacationLeave = 20;
        } elseif ($yearsOfService >= 8) {
            $vacationLeave = 15;
        } elseif ($yearsOfService >= 0) {
            $vacationLeave = 10;
        } 
    
        $entitlements = [
            'Day/s off | Annual Vacation' => $vacationLeave,
            'Sick'            => 10,
            'Compassionate'   => 7,
            'Maternity'       => 60,
            'Paternity'       => 7,
        ];
    
        $leaveBalances = [];
    
        foreach ($entitlements as $typeName => $allowedDays) {
            $leaveType = Leavetype::where('type', $typeName)->first();
    
            if (!$leaveType) {
                continue; // skip if leave type not found
            }
    
            $usedDays = LeaveReq::where('userid', $userId)
                ->where('leavetypeid', $leaveType->id)
                ->where(function($query) {
                    $query->where('dept_head', 'Approved')
                          ->orWhere('dept_head', 'None');
                })
                
                ->where('exec_sec', 'Approved')
                ->where('president', 'Approved')
                ->selectRaw('SUM(DATEDIFF(`to`, `from`) + 1) as used')
                ->value('used') ?? 0;
    
            $leaveBalances[] = [
                'type' => $typeName,
                'allowed' => $allowedDays,
                'used' => $usedDays,
                'remaining' => max($allowedDays - $usedDays, 0),
            ];
        }
    
        return response()->json([
            'years_of_service' => $yearsOfService,
            'balances' => $leaveBalances,
        ]);
    }
    

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

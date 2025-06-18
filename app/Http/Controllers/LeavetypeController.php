<?php

namespace App\Http\Controllers;

use App\Models\leavetype;
use App\Http\Requests\StoreleavetypeRequest;
use App\Http\Requests\UpdateleavetypeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveReq;
use App\Models\yearsofservice;
use Illuminate\Support\Facades\DB;
class LeavetypeController extends Controller
{   
    
   public function getLeaveBalances($userId)
{
    $user = User::findOrFail($userId);
    $currentYear = date('Y');
    $isNewYear = (date('m-d') == '01-01'); // Check if today is Jan 1st

    // Years of service calculation
    $yearsofservice = yearsofservice::where('userid', $userId)
        ->select('start_date', 'end_date')
        ->get();

    $totalMonths = 0;
    foreach ($yearsofservice as $service) {
        $start = Carbon::parse($service->start_date);
        $end = $service->end_date ? Carbon::parse($service->end_date) : Carbon::now();
        $totalMonths += $start->diffInMonths($end);
    }

    $yearsOfService = floor($totalMonths / 12);
    $remainingMonths = $totalMonths % 12;
    $summary = "{$yearsOfService} years" . ($remainingMonths > 0 ? ", {$remainingMonths} months" : "");

    // Determine base vacation leave entitlement
    $baseVacationLeave = match(true) {
        $yearsOfService >= 16 => 20,
        $yearsOfService >= 8 => 15,
        $yearsOfService >= 1 => 10,
        default => 0
    };

    // Get previous year's unused vacation
    $prevYear = $currentYear - 1;
    $prevYearVacationType = Leavetype::where('type', 'Day/s off | Annual Vacation')->first();
    
    $prevYearUnusedVacation = 0;
    
    if ($prevYearVacationType) {
        $prevYearUsed = LeaveReq::where('userid', $userId)
            ->where('leavetypeid', $prevYearVacationType->id)
            ->whereYear('from', $prevYear)
            ->where(function ($query) {
                $query->where('dept_head', 'Approved')
                      ->orWhere('dept_head', 'None');
            })
            ->where('exec_sec', 'Approved')
            ->where('president', 'Approved')
            ->selectRaw('SUM(DATEDIFF(`to`, `from`) + 1) as used')
            ->value('used') ?? 0;

        $prevYearUnusedVacation = max($baseVacationLeave - $prevYearUsed, 0);
    }

    // On Jan 1st, move unused vacation to Others (if not already done)
    if ($isNewYear && $prevYearUnusedVacation > 0) {
        // Find or create the Others leave type
        $othersType = Leavetype::firstOrCreate(
            ['type' => 'Others'],
            ['days_allowed' => 0, 'reason' => 'Carried over vacation days']
        );

        // Create a special leave balance entry for carried-over days
        DB::table('carried_over_leave')->updateOrInsert(
            ['userid' => $userId, 'year' => $currentYear],
            ['days' => $prevYearUnusedVacation, 'expires_at' => Carbon::create($currentYear, 12, 31)]
        );
    }

    // Get current carried over days (if any)
    $carriedOverDays = DB::table('carried_over_leave')
        ->where('userid', $userId)
        ->where('year', $currentYear)
        ->value('days') ?? 0;

    // Prepare entitlements
    $entitlements = [
        'Day/s off | Annual Vacation' => $baseVacationLeave,
        'Sick'            => 10,
        'Compassionate'   => 7,
        'Maternity'       => 60,
        'Paternity'       => 7,
        'Others'          => $carriedOverDays, // Include carried over days
    ];

    $leaveBalances = [];

    foreach ($entitlements as $typeName => $allowedDays) {
        $leaveType = Leavetype::where('type', $typeName)->first();
        if (!$leaveType) continue;

        $usedDays = LeaveReq::where('userid', $userId)
            ->where('leavetypeid', $leaveType->id)
            ->whereYear('from', $currentYear)
            ->where(function ($query) {
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
            'is_carried_over' => ($typeName === 'Others' && $carriedOverDays > 0),
        ];
    }

    return response()->json([
        'years_of_service' => [
            'years' => $yearsOfService,
            'months' => $remainingMonths,
            'summary' => $summary
        ],
        'balances' => $leaveBalances,
        'carried_over_days' => $carriedOverDays,
        'carried_over_expiry' => $currentYear . '-12-31'
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

<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SpouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\EmploymentdetController;
use App\Http\Controllers\EmploymenteducController;
use App\Http\Controllers\EmpfamilyController;
use App\Models\designation;
use App\Http\Controllers\LeaveReqController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\CalendardaysController;
// Protected Route (Requires Authentication via Sanctum)
// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::post('/users', [EmployeesController::class, 'store']);
// });

// Route::middleware(['auth:sanctum'])->group(function () {
// });
Route::post('/login',[EmployeesController::class,'login']);
Route::post('/regusers', [EmployeesController::class, 'store']);
Route::get('/users/count', [EmployeesController::class, 'count']);


Route::put('/employees/{id}', [EmployeesController::class, 'update']);
Route::get('/employees', [EmployeesController::class, 'index']);
Route::delete('/employees/{id}', [EmployeesController::class, 'destroy']);
Route::get('/employees/{id}', [EmployeesController::class, 'show']);

Route::put('/acceptemployees/{id}', [EmployeesController::class, 'acceptemployee']);
Route::post('/accountsaveedit', [EmployeesController::class, 'accountsaveedit']);

Route::get('/account/{userid}', [EmployeesController::class, 'getAccountDetails']);

Route::apiResource('employeefamily', EmpfamilyController::class);
Route::apiResource('spouse', SpouseController::class);

Route::apiResource('employmentdetails', EmploymentdetController::class);

Route::apiResource('employmenteducs', EmploymenteducController::class);


Route::apiResource('leaverequests', LeaveReqController::class);
Route::get('/leaverequests/user/{userid}', [LeaveReqController::class, 'showByUserId']);
Route::delete('/leaverequests/{id}', [LeaveReqController::class, 'destroy']);
Route::put('/leave-requests/userupdate/{id}', [LeaveReqController::class, 'updateDetails']);
Route::put('/leave-reqs/{id}/approve', [LeaveReqController::class, 'approveLeaveRequest']);
Route::put('/leave-reqs/{id}/reject', [LeaveReqController::class, 'rejectLeaveRequest']);

Route::apiResource('leave-types', LeaveTypeController::class);
Route::apiResource('department', DepartmentController::class);
Route::apiResource('designation', DesignationController::class);
Route::apiResource('position', PositionController::class);
Route::apiResource('announcements', AnnouncementController::class);

Route::apiResource('events', EventsController::class);

Route::post('/upload', function (Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('uploads', 'public'); // Store in 'storage/app/public/uploads'
        return response()->json(['fileUrl' => asset('storage/' . $path)]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
});
Route::post('/upload-image', [EmployeesController::class, 'uploadImage']);
Route::get('assets/userPic/{filename}', function ($filename) {
        $path = public_path('assets/userPic/' . $filename);
        
        if (file_exists($path)) {
            return response()->file($path);
        }
    
        abort(404);
});

Route::middleware('auth:sanctum')->post('/logout',[EmployeesController::class,'logout']);


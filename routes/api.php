<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\PositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\LeaveTypeController;
use App\Models\designation;

// Protected Route (Requires Authentication via Sanctum)
// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::post('/users', [EmployeesController::class, 'store']);
// });
// Public Route to Store Users (if you want it to be accessible without authentication)

Route::post('/login',[EmployeesController::class,'login']);
Route::post('/regusers', [EmployeesController::class, 'store']);
Route::get('/users/count', [EmployeesController::class, 'count']);

Route::put('/employees/{id}', [EmployeesController::class, 'update']);
Route::get('/employees', [EmployeesController::class, 'index']);
Route::delete('/employees/{id}', [EmployeesController::class, 'destroy']);
Route::get('/employees/{id}', [EmployeesController::class, 'show']);

Route::apiResource('leave-types', LeaveTypeController::class);
Route::apiResource('department', DepartmentController::class);
Route::apiResource('designation', DesignationController::class);
Route::apiResource('position', PositionController::class);
Route::apiResource('announcements', AnnouncementController::class);

Route::post('/upload', function (Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('uploads', 'public'); // Store in 'storage/app/public/uploads'
        return response()->json(['fileUrl' => asset('storage/' . $path)]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
});

Route::middleware('auth:sanctum')->post('/logout',[EmployeesController::class,'logout']);


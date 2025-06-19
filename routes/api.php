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
use App\Http\Controllers\RequestfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WorkstatusController;
use App\Http\Controllers\CertificatesController;
use App\Http\Controllers\YearsofserviceController;

Route::post('/login', [EmployeesController::class, 'login']);
// ->middleware('throttle:5,1')

Route::apiResource('requestfile', RequestfileController::class);
Route::post('/upload-files', [RequestfileController::class, 'uploadFiles'])->middleware('web');
Route::post('/store-or-update-acc-code/{id}', [RequestfileController::class, 'storeOrUpdateAccCode']);
Route::get('/requestfile/records/{userId}', [RequestfileController::class, 'getrecordsByUserId']);
Route::get('/filerecords', [RequestfileController::class, 'bynotsoa']);
Route::get('/soarecords', [RequestfileController::class, 'bysoa']);
Route::post('/upload', function (Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('uploads', 'public'); // Store in 'storage/app/public/uploads'
        return response()->json(['fileUrl' => asset('storage/' . $path)]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
});
Route::post('/upload-image', [EmployeesController::class, 'uploadImage']);
Route::apiResource('announcements', AnnouncementController::class);

Route::middleware(['auth:sanctum'])->group(function () {

Route::get('/users/count', [EmployeesController::class, 'count']);
Route::get('/leavecount', [EmployeesController::class, 'countdashadmin']);
Route::get('/presapplieadleave', [EmployeesController::class, 'countleavepresident']);
Route::get('/dheadapplieadleave/{id}', [EmployeesController::class, 'countleavedepthead']);
Route::get('/dashboard-data/{userId}',  [EmployeesController::class, 'countTodayEventsAndApprovedLeaves']);
Route::put('/employees/{id}', [EmployeesController::class, 'update']);
Route::get('/employees', [EmployeesController::class, 'index']);
Route::delete('/employees/{id}', [EmployeesController::class, 'destroy']);
Route::get('/employees/{id}', [EmployeesController::class, 'show']);
Route::put('/acceptemployees/{id}', [EmployeesController::class, 'acceptemployee']);
Route::post('/accountsaveedit', [EmployeesController::class, 'accountsaveedit']);
Route::get('/account/{userid}', [EmployeesController::class, 'getAccountDetails']);
Route::apiResource('employeefamily', EmpfamilyController::class);
Route::apiResource('spouse', SpouseController::class);

Route::apiResource('leaverequests', LeaveReqController::class);
Route::get('/leave-requests/dhead/{id}', [LeaveReqController::class, 'getByDHead']);
Route::get('/executivesec', [LeaveReqController::class, 'getexecutivesec']);
Route::get('/president', [LeaveReqController::class, 'getbypresident']);
Route::get('/departmentheads', [LeaveReqController::class, 'getDepartmentHeads']);
Route::get('/leaverequests/user/{userid}', [LeaveReqController::class, 'showByUserId']);
Route::delete('/leaverequests/{id}', [LeaveReqController::class, 'destroy']);
Route::put('/leave-requests/userupdate/{id}', [LeaveReqController::class, 'updateDetails']);
// dhead 
Route::put('/leave-reqs/{id}/approve', [LeaveReqController::class, 'approveLeaveRequest']);
Route::put('/leave-reqs/{id}/reject', [LeaveReqController::class, 'rejectLeaveRequest']);
// execsec
Route::put('/leave-execsec/{id}/approve', [LeaveReqController::class, 'approveLeaveRequestexecsec']);
Route::put('/leave-execsec/{id}/reject', [LeaveReqController::class, 'rejectLeaveRequestexecsec']);
// pres 
Route::put('/leave-pres/{id}/approve', [LeaveReqController::class, 'approveLeaveRequestpres']);
Route::put('/leave-pres/{id}/reject', [LeaveReqController::class, 'rejectLeaveRequestpres']);
Route::get('/leave-count/{userid}', [LeaveReqController::class, 'countLeaveAndEvents']);

Route::put('/leave-requests/{id}', function (Request $request, $id) {
    DB::table('leave_reqs')
        ->where('id', $id)
        ->update($request->only(['from', 'to']));
    
    return ['success' => true];
});
Route::apiResource('employmentdetails', EmploymentdetController::class);
Route::apiResource('employmenteducs', EmploymenteducController::class);
Route::apiResource('leave-types', LeaveTypeController::class);

Route::get('/leave-getLeaveBalances/{userid}', [LeaveTypeController::class, 'getLeaveBalances']);

Route::apiResource('department', DepartmentController::class);
Route::apiResource('category', CategoryController::class);
Route::apiResource('workstatus', WorkstatusController::class);
Route::apiResource('designation', DesignationController::class);
Route::apiResource('position', PositionController::class);

//uploadIMG.....

Route::get('/notifications/{userId}', [NotificationController::class, 'getnotif']); 
Route::get('/notify-exec-secretary', [NotificationController::class, 'notifyExecutiveSecretary']);
Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications', [NotificationController::class, 'index']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::get('/notifications/unread-count/{userId}', [NotificationController::class, 'getUnreadNotificationCount']);

Route::apiResource('events', EventsController::class);  
// Route::get('/events/{id}', [EmployeEventsControlleresController::class, 'show']);
Route::get('/events/user/{userId}', [EventsController::class, 'getEventsByUserId']);
Route::post('/nlmevents', [EventsController::class, 'storeForAllUsers']);


Route::get('assets/userPic/{filename}', function ($filename) {
        $path = public_path('assets/userPic/' . $filename);
        
        if (file_exists($path)) {
            return response()->file($path);
        }
    
        abort(404);
});
Route::post('/regusers', [EmployeesController::class, 'store']);


Route::post('/post-cert', [CertificatesController::class, 'postcert']);
Route::get('/certbyid/{userid}', [CertificatesController::class, 'showcertbyid']);
Route::delete('/delcertificates/{id}', [CertificatesController::class, 'deletecert']);

});


Route::middleware('auth:sanctum')->post('/logout',[EmployeesController::class,'logout']);


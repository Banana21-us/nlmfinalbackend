<?php

namespace App\Http\Controllers;

use App\Models\employees;
use App\Http\Requests\StoreemployeesRequest;
use App\Http\Requests\UpdateemployeesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\announcement;
use App\Models\Employmenteduc;
use App\Models\Employmentdet;
use App\Models\empfamily;
use App\Models\spouse;
use App\Models\notification;
use App\Models\LeaveReq;
use Carbon\Carbon;
use App\Models\events;
class EmployeesController extends Controller
{
    public function login(Request $request) {
        Log::info('Login request received.', ['request_data' => $request->all()]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = User::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            Log::warning('Login failed: Incorrect credentials.', ['email' => $request->email]);
            return response()->json([
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }

        // $token = $admin->createToken($admin->name);
        $token = $admin->createToken('AuthToken');
        Log::info('Login successful.', ['admin_id' => $admin->id]);

        return response()->json([
            'admin' => $admin,
            'position' => $admin->position, // Ensure this is correct
            'token' => $token->plainTextToken,
            'id' => $admin->id,
            'department' => $admin->department,
            'position' => $admin->position,
            'designation' => $admin->designation,
        ]);
    }
    public function logout(Request $request){
        if ($request->user()) {
            $request->user()->tokens()->delete();

            return [
                'message' => 'You are logged out',
            ];
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    } 
    public function uploadImage(Request $request){
        Log::info('Image upload request received.', ['request' => $request->all()]);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::findOrFail($request->input('id'));
            Log::info('User found:', ['user_id' => $user->id, 'current_img' => $user->img]);

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('assets/userPic');

            Log::info('Image details:', ['filename' => $imageName, 'path' => $destinationPath]);

            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
                Log::info('Created directory:', ['path' => $destinationPath]);
            }

            if ($user->img && file_exists($path = $destinationPath . '/' . $user->img)) {
                unlink($path);
                Log::info('Deleted old image:', ['path' => $path]);
            }

            $image->move($destinationPath, $imageName);
            $user->update(['img' => $imageName]);

            Log::info('Image uploaded successfully.', ['user_id' => $user->id, 'new_image' => $imageName]);

            return response()->json([
                'message' => 'Image uploaded successfully.',
                'image_url' => url('assets/userPic/' . $imageName)
            ]);
        } catch (\Exception $e) {
            Log::error('Image upload failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Image upload failed.'], 500);
        }

    }
    public function count() {
        $pendingUsers = User::whereNull('reg_approval')->count(); 
        $approvedUsers = User::whereNotNull('reg_approval')->count(); 
        $announcementCount = Announcement::count(); 

        return response()->json([
            'pending_users' => $pendingUsers,
            'approved_users' => $approvedUsers,
            'total_announcements' => $announcementCount,
        ]);
    }
    public function countdashadmin() {
        $pendingExecSecCount = LeaveReq::where('dept_head', 'Approved')
                                       ->where('exec_sec', 'Pending')
                                       ->count();
        return response()->json([
            'pending_exec_sec' => $pendingExecSecCount
        ]);
    }
    public function countleavepresident() {
        $pendingPres = LeaveReq::where('dept_head', 'Approved')
                                       ->where('exec_sec', 'Approved')
                                       ->where('president', 'Pending')
                                       ->count();
        return response()->json([
            'pending_pres' => $pendingPres
        ]);
    }     
    public function countleavedepthead($id){
        $pendingdhead = LeaveReq::where('dept_head', 'Pending')
                                ->where('exec_sec', 'Pending')
                                ->where('president', 'Pending')
                                ->count();

        return response()->json([
            'pending_dhead' => $pendingdhead
        ]);
    }
    public function countTodayEventsAndApprovedLeaves($userId) {
        $today = Carbon::today()->toDateString();
    
        // Count events for today
        $eventCount = Events::where('userid', $userId)
                            ->whereDate('time', $today)
                            ->count();
    
        // Sum total approved leave days for the specific user
        $totalLeaveDays = LeaveReq::where('userid', $userId)
                                  ->where('president', 'Approved')
                                  ->get()
                                  ->sum(function ($leave) {
                                      return Carbon::parse($leave->from)->diffInDays(Carbon::parse($leave->to)) + 1;
                                  });
    
        return response()->json([
            'today_events' => $eventCount,
            'total_leave_days' => $totalLeaveDays,
        ]);
    }
    public function accountsaveedit(Request $request)
    {
        Log::info('Starting accountsaveedit method');

        try {
            // Log request data
            Log::info('Raw request data:', [$request->getContent()]);
            Log::info('Parsed request data:', $request->all());

            // Validate incoming request data
            $validatedData = $request->validate([
                'userid' => 'required|exists:users,id',
                'name' => 'required|string',
                'phone_number' => 'required|numeric',
                'email' => 'required|email',
                'address' => 'nullable|string',
                'status' => 'nullable|string',
                'birthplace' => 'nullable|string',
                'spouse' => 'nullable|string',
                'marriageDate' => 'nullable|date',
                'children' => 'nullable|array',
                'children.*.name' => 'required|string',
                'children.*.dateofbirth' => 'required|date',
                'children.*.career' => 'nullable|string',
                'employments' => 'nullable|array',
                'employments.*.position' => 'required|string',
                'employments.*.dateofemp' => 'nullable|date',
                'employments.*.organization' => 'required|string',
                'education' => 'nullable|array',
                'education.*.level' => 'required|string',
                'education.*.year' => 'required|date',
                'education.*.school' => 'required|string',
                'education.*.degree' => 'nullable|string',
                // 'password' => 'nullable|string|min:8', 
                'old_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8',
                'confirm_password' => 'nullable|string|same:new_password'
            ]);

            Log::info('Request data validated successfully.');

            // Find the user to update
            $user = User::findOrFail($request->userid);

            Log::info('User found:', ['user_id' => $user->id, 'user_name' => $user->name]);

            // Update user basic info
            $user->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'address' => $request->address,
                'status' => $request->status,
                'birthplace' => $request->birthplace,
            ]);
            
            // Update password if provided
            // if ($request->filled('password')) {
            //     $user->password = bcrypt($request->password);
            //     $user->save();
            //     Log::info('Password updated.');
            // }
            if ($request->filled('old_password')) {
                // Check if the old password matches the current one
                if (!Hash::check($request->old_password, $user->password)) {
                    Log::warning('Old password does not match.');
                    return response()->json(['message' => 'Old password is incorrect'], 400);
                }
            
                // Check if new password and confirm password match
                if ($request->new_password !== $request->confirm_password) {
                    Log::warning('New password and confirm password do not match.');
                    return response()->json(['message' => 'Passwords do not match'], 400);
                }
            
                // Update to the new password
                $user->password = bcrypt($request->new_password);
                $user->save();
                Log::info('Password updated successfully.');
            }

            Log::info('User basic info updated successfully.');

            // Handle education records
            if ($request->has('education')) {
                Employmenteduc::where('userid', $request->userid)->delete(); // Remove old education records
                Log::info('Existing education records removed.');

                foreach ($request->education as $edu) {
                    Employmenteduc::create([
                        'userid' => $request->userid,
                        'levels' => $edu['level'],
                        'year' => $edu['year'],
                        'school' => $edu['school'],
                        'degree' => $edu['degree'] ?? null,
                    ]);
                    Log::info('Education record created:', ['level' => $edu['level'], 'school' => $edu['school']]);
                }
                Log::info('Education information updated/created successfully.');
            } else {
                Log::info('No education data provided in the request.');
            }

            // Handle spouse information
            $spouseData = [
                'userid' => $request->userid,
                'name' => $request->spouse,
                'dateofmarriage' => $request->dateofmarriage,
            ];

            Spouse::updateOrCreate(['userid' => $request->userid], $spouseData);

            Log::info('Spouse information updated/created successfully.');

            // Handle children
            Empfamily::where('userid', $request->userid)->delete(); // Remove old children first
            Log::info('Existing children removed.');

            if ($request->has('children')) {
                foreach ($request->children as $child) {
                    Empfamily::create([
                        'userid' => $request->userid,
                        'children' => $child['name'],
                        'dateofbirth' => $child['dateofbirth'],
                        'career' => $child['career'],
                    ]);
                    Log::info('Child created:', ['child_name' => $child['name']]);
                }
                Log::info('Children information updated/created successfully.');
            } else {
                Log::info('No children data provided in the request.');
            }

            // Handle employments
            if ($request->has('employments')) {
                Employmentdet::where('userid', $request->userid)->delete(); // Remove old employments first
                Log::info('Existing employments removed.');
                foreach ($request->employments as $employment) {
                    Employmentdet::create([
                        'userid' => $request->userid,
                        'position' => $employment['position'],
                        'dateofemp' => $employment['dateofemp'],
                        'organization' => $employment['organization'],
                    ]);
                    Log::info('Employment created:', ['position' => $employment['position']]);
                }
                Log::info('Employment information updated/created successfully.');
            } else {
                Log::info('No employment data provided in the request.');
            }

            Log::info('Account data saved successfully.');

            return response()->json(['message' => 'Account data saved successfully'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::error('Validation errors:', $e->errors());
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            // Log any other exceptions that occur
            Log::error('Error saving account data: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
            return response()->json(['message' => 'Error saving account data: ' . $e->getMessage()], 500);
        }
    }

    public function getAccountDetails($userid){
        Log::info('Fetching account details for user', ['userid' => $userid]);

        try {
            // Fetch the user
            $user = User::where('id', $userid)->firstOrFail();

            // Fetch employment education details
            $education = Employmenteduc::where('userid', $userid)
                ->select('levels as level', 'year', 'school', 'degree')
                ->get();

            // Fetch employment history
            $employments = Employmentdet::where('userid', $userid)
                ->select('position', 'dateofemp', 'organization')
                ->get();

            // Prepare account data
            $accountData = [
                'userid' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->address,
                'birthdate' => $user->birthdate,
                'birthplace' => $user->birthplace,
                'img' => $user->img, 
                'department' => $user->department, 
                'position' => $user->position, 
                'designation' => $user->designation,
                'status' => $user->status,
                'education' => $education,
                'employments' => $employments,
                
            ];

            // Check marital status before fetching spouse and children
            if ($user->status !== 'Single') {
                $spouse = Spouse::where('userid', $userid)->select('name', 'dateofmarriage')->first();
                $children = Empfamily::where('userid', $userid)
                    ->select('children as name', 'dateofbirth', 'career')
                    ->get();

                $accountData['spouse'] = $spouse;
                $accountData['dateofmarriage'] = $spouse ? $spouse->dateofmarriage : null;
                $accountData['children'] = $children;
            }

            Log::info('Account details retrieved successfully.', ['userid' => $userid]);

            return response()->json(['data' => $accountData], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching account details: ' . $e->getMessage(), ['userid' => $userid]);

            return response()->json(['message' => 'Error fetching account details'], 500);
        }
    }
    public function index(){
        $employees = User::orderBy('created_at', 'desc')->get();
        return response()->json($employees);
    }
    public function store(Request $request){
        Log::info('Received a request to create a new user.', ['request_data' => $request->all()]);

        $request->validate([
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'birthplace' => 'required|string|max:255',
            'phone_number' => 'required|numeric',
            'gender' => 'required|in:Male,Female',
            'status' => 'nullable|string|in:Single,Married',
            'address' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'work_status' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'reg_approval' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            
        ]);

        Log::info('Validation successful for user creation.');

        try {
            $user = User::create([
                'name' => $request->name,
                'birthdate' => $request->birthdate,
                'birthplace' => $request->birthplace,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'address' => $request->address,
                'status' => $request->status,
                'email' => $request->email,

                'department' => $request->department,
                'position' => $request->position,
                'designation' => $request->designation,
                'work_status' => $request->work_status,
                'category' => $request->category,

                'password' => Hash::make($request->password),
            ]);

            Log::info('User created successfully.', ['user_id' => $user->id]);

            $this->notifyExecutiveSecretary();

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
            if ($errors = $request->errors()) {
                Log::error('Validation failed:', ['errors' => $errors]);
            }
            
        } catch (\Exception $e) {
            Log::error('User creation failed.', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to create user', 'error' => $e->getMessage()], 500);
        }
    }
    public function notifyExecutiveSecretary(){
        // Find the Human Resource (positions may contain multiple roles separated by commas)
        $executiveSecretary = User::where('position', 'LIKE', '%Human Resource%')->first();

    
        if (!$executiveSecretary) {
            Log::warning('Human Resource not found.');
            return response()->json(['message' => 'Human Resource not found'], 404);
        }   
    
        // Find all users with pending registration approval
        $pendingUsers = User::whereNull('reg_approval')->pluck('name');
    
        if ($pendingUsers->isEmpty()) {
            Log::info('No pending user registrations.');
            return response()->json(['message' => 'No pending user registrations'], 200);
        }
    
        // Fetch existing notifications for the executive secretary
        $existingNotifications = Notification::where('userid', $executiveSecretary->id)
            ->whereIn('message', $pendingUsers->map(fn ($name) => "New user request pending approval: \n{$name}"))
            ->pluck('message')
            ->toArray();
    
        // Prepare new notifications
        $notifications = [];
        foreach ($pendingUsers as $name) {
            $message = "New user request pending approval: \n{$name}";
    
            // Only add if it does not already exist
            if (!in_array($message, $existingNotifications)) {
                $notifications[] = [
                    'userid'      => $executiveSecretary->id,
                    'type'        => "User Request",
                    'message'     => $message,
                    'is_read'     => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
    
        // ✅ **Insert notifications if there are new ones**
        if (!empty($notifications)) {
            Notification::insert($notifications);
            Log::info('New notifications saved.', ['notifications' => $notifications]);
        } else {
            Log::info('No new notifications to save.');
        }
    
        return response()->json(['message' => 'Notification sent successfully']);
    }
    public function show($id){
        $employee = User::find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Fetch related education and employment details
        $education = Employmenteduc::where('userid', $id)->get();
        $employment = Employmentdet::where('userid', $id)->get();
        $employfamily = empfamily::where('userid', $id)->get();
        $spouse = spouse::where('userid', $id)->get();

        return response()->json([
            'employee' => $employee,
            'education' => $education,
            'employment' => $employment,
            'employfamily' => $employfamily,
            'spouse' => $spouse,
        ]);
    }
    public function acceptemployee(Request $request, $id){
        Log::info("Received request to accept employee", ['employee_id' => $id, 'request_data' => $request->all()]);

        // Find the employee by ID
        $employee = User::find($id);
        
        if (!$employee) {
            Log::warning("Employee not found", ['employee_id' => $id]);
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Validate request data
        $validatedData = $request->validate([
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'work_status' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ]);

        Log::info("Validation passed", ['validated_data' => $validatedData]);

        // Update fields if provided
        $employee->department = $request->department;
        $employee->position = $request->position;
        $employee->designation = $request->designation;
        $employee->work_status = $request->work_status;
        $employee->category = $request->category;
        $employee->reg_approval = now()->toDateString(); 

        $employee->save();

        Log::info("Employee updated successfully", ['employee_id' => $id]);

        return response()->json([
            'message' => 'Employee updated successfully',
            'employee' => $employee
        ]);
    }
    public function update(Request $request, $id){
        Log::info('Received request to update employee', ['employee_id' => $id, 'request_data' => $request->all()]);

        // Find the employee by ID
        $employee = User::find($id);

        // If employee not found, return error
        if (!$employee) {
            Log::warning('Employee not found', ['employee_id' => $id]);
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Validate request data
        $request->validate([
            'department' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255|nullable',
            'designation' => 'sometimes|string|max:255|nullable',
            'work_status' => 'sometimes|string|max:255|nullable',
            'category' => 'sometimes|string|max:255|nullable',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6|nullable',
        ]);

        Log::info('Validation passed', ['validated_data' => $request->all()]);

        // Update fields if provided
        if ($request->has('department')) {
            $employee->department = $request->department;
        }
        if ($request->has('position')) {
            $employee->position = $request->position;
        }
        if ($request->has('designation')) {
            $employee->designation = $request->designation;
        }
        if ($request->has('work_status')) {
            $employee->work_status = $request->work_status;
        }
        if ($request->has('category')) {
            $employee->category = $request->category;
        }
        if ($request->has('email')) {
            $employee->email = $request->email;
        }
        // Only update password if it is not null, otherwise keep the old password
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        } else {
            $employee->password = $employee->getOriginal('password');
        }

        // Save the updated data
        $employee->save();

        Log::info('Employee updated successfully', ['employee_id' => $id]);

        // Return success response
        return response()->json([
            'message' => 'Employee updated successfully',
            'employee' => $employee
        ]);
    }
    public function destroy($id){
        $employee = User::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}

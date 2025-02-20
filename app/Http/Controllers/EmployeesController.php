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
class EmployeesController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $admin = User::where('email', $request->email)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }
        $token = $admin->createToken($admin->name);
        return response()->json([
            'admin' => $admin,
            'token' => $token->plainTextToken,
            'id' => $admin->id
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
    
    public function count(){
        $userCount = User::count(); // Get total user count
        $announcementCount = Announcement::count(); // Get total announcement count
    
        return response()->json([
            'total_users' => $userCount,
            'total_announcements' => $announcementCount,
        ]);
    }
    public function empregistration(){
        
    }
    public function index(){
        // $employees = User::select('name', 'department', 'designation')->get();
        $employees = User::all();
        return response()->json($employees);
    }
    
    /**
     * Store a newly created resource in storage.
     */
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
                'password' => Hash::make($request->password),
                'reg_approval' => null,
            ]);

            Log::info('User created successfully.', ['user_id' => $user->id]);

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
            if ($errors = $request->errors()) {
                Log::error('Validation failed:', ['errors' => $errors]);
            }
            
        } catch (\Exception $e) {
            Log::error('User creation failed.', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to create user', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
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
    ]);

    Log::info("Validation passed", ['validated_data' => $validatedData]);

    // Update fields if provided
    $employee->department = $request->department;
    $employee->position = $request->position;
    $employee->designation = $request->designation;
    $employee->reg_approval = now()->toDateString(); 

    $employee->save();

    Log::info("Employee updated successfully", ['employee_id' => $id]);

    return response()->json([
        'message' => 'Employee updated successfully',
        'employee' => $employee
    ]);
}
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    // Find the employee by ID
    $employee = User::find($id);

    // If employee not found, return error
    if (!$employee) {
        return response()->json(['message' => 'Employee not found'], 404);
    }

    // Validate request data
    $request->validate([
        'department' => 'sometimes|string|max:255',
        'position' => 'sometimes|string|max:255|nullable',
        'designation' => 'sometimes|string|max:255|nullable',
        'email' => 'sometimes|email|unique:users,email,' . $id,
        'password' => 'sometimes|string|min:6|nullable',
    ]);

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
    if ($request->has('email')) {
        $employee->email = $request->email;
    }
    if ($request->has('password')) {
        $employee->password = bcrypt($request->password);
    }

    // Save the updated data
    $employee->save();

    // Return success response
    return response()->json([
        'message' => 'Employee updated successfully',
        'employee' => $employee
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = User::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}

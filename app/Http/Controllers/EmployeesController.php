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
class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
    // Validate the incoming request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // Find the user by email
    $admin = User::where('email', $request->email)->first();

    // Check if user exists and password matches
    if (!$admin || !Hash::check($request->password, $admin->password)) {
        // Return a JSON response with error message and 401 status code
        return response()->json([
            'message' => 'The provided credentials are incorrect'
        ], 401);
    }

    // Generate token after successful login
    $token = $admin->createToken($admin->name);

    // Return a JSON response with the admin details and token
    return response()->json([
        'admin' => $admin,
        'token' => $token->plainTextToken,
        'id' => $admin->id
    ]);
    }
    public function logout(Request $request)
    {
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

    public function count()
    {
        $userCount = User::count(); // Get total user count
        $announcementCount = Announcement::count(); // Get total announcement count
    
        return response()->json([
            'total_users' => $userCount,
            'total_announcements' => $announcementCount,
        ]);
    }

    public function index()
    {
        // $employees = User::select('name', 'department', 'designation')->get();
        $employees = User::all();
        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    Log::info('Received a request to create a new user.', ['request_data' => $request->all()]);

    $request->validate([
        'name' => 'required|string|max:255',
        'birthdate' => 'required|date',
        'birthplace' => 'required|string|max:255',
        'phone_number' => 'required|numeric',
        'gender' => 'required|in:Male,Female',
        'status' => 'nullable|string|in:Single,Married',
        'address' => 'required|string|max:255',
        'department' => 'required|string|max:255',
        'position' => 'nullable|string|max:255',
        'designation' => 'nullable|string|max:255',
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
            'stauts' => $request->address,
            'department' => $request->department,
            'position' => $request->position,
            'designation' => $request->designation,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
        if ($employee) {
            return response()->json($employee);
        } else {
            return response()->json(['error' => 'Employee not found'], 404);
        }
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

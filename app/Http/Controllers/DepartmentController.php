<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\department;
use App\Http\Requests\StoredepartmentRequest;
use App\Http\Requests\UpdatedepartmentRequest;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // You can list all departments here (if needed)
        $departments = Department::all();
        return response()->json($departments);
            
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Create a new department using the validated data from the request
        $department = Department::create([
            'name' => $request->name,
        ]);


        // Return the created leave type
        return response()->json($department, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {

        $formField = $request->validate([
            'name' => 'required|max:255',
        ]);

        $department->update($formField);
        return $department;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Delete the department
        $department->delete();

        return response()->json(['message' => 'Department deleted successfully!']);
    }
}

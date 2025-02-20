<?php

namespace App\Http\Controllers;

use App\Models\employmenteduc;
use App\Http\Requests\StoreemploymenteducRequest;
use App\Http\Requests\UpdateemploymenteducRequest;
use Illuminate\Http\Request;
class EmploymenteducController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all employment education records
        $employmenteduc = Employmenteduc::with('user')->get();
        return response()->json($employmenteduc);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate incoming request
    $request->validate([
        'userid' => 'required|exists:users,id',
        'elementary' => 'nullable|string|max:255',
        'highschool' => 'nullable|string|max:255',
        'college' => 'nullable|string|max:255',
        'gradschool' => 'nullable|string|max:255',
    ]);

    // Create a new education record
    $employmenteduc = Employmenteduc::create($request->all());

    return response()->json($employmenteduc, 201);
}


    /**
     * Display the specified resource.
     */
    public function show($id)
{   
    
    $employmenteduc = Employmenteduc::with('user')->find($id);

    if (!$employmenteduc) {
        return response()->json(['error' => 'Record not found'], 404);
    }

    return response()->json($employmenteduc, 200);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $employmenteduc = Employmenteduc::find($id);

    if (!$employmenteduc) {
        return response()->json(['error' => 'Record not found'], 404);
    }

    // Validate request data
    $request->validate([
        'userid' => 'required|exists:users,id',
        'levels' => 'nullable|string|max:255',
        'year' => 'nullable|string|max:255',
        'school' => 'nullable|string|max:255',
        'desgree' => 'nullable|string|max:255',
    ]);

    // Update record
    $employmenteduc->update($request->all());

    return response()->json($employmenteduc, 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employmenteduc = Employmenteduc::find($id);

        if (!$employmenteduc) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $employmenteduc->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }
}

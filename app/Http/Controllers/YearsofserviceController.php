<?php

namespace App\Http\Controllers;

use App\Models\yearsofservice;
use App\Http\Requests\StoreyearsofserviceRequest;
use App\Http\Requests\UpdateyearsofserviceRequest;
use Illuminate\Http\Request;
class YearsofserviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employmenteduc = Yearsofservice::with('user')->get();
        return response()->json($employmenteduc);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    
    // Validate incoming request
    $validated = $request->validate([
        'userid' => 'required|exists:users,id',
        'organization' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    // Create the record
    $service = Yearsofservice::create($validated);

    // Return a response
    return response()->json([
        'message' => 'Years of Service record created successfully.',
        'data' => $service
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $yearsofservice = Yearsofservice::with('user')->find($id);

        if (!$yearsofservice) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json($yearsofservice, 200);
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateyearsofserviceRequest $request, yearsofservice $yearsofservice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(yearsofservice $yearsofservice)
    {
        //
    }
}

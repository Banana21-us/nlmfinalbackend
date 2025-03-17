<?php

namespace App\Http\Controllers;

use App\Models\designation;
use App\Http\Requests\StoredesignationRequest;
use App\Http\Requests\UpdatedesignationRequest;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designation = designation::all();
        return response()->json($designation);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Create a new designation using the validated data from the request
        $designation = designation::create([
            'name' => $request->name,
        ]);


        // Return the created leave type
        return response()->json($designation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(designation $designation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, designation $designation)
    {

        $formField = $request->validate([
            'name' => 'required|max:255',
        ]);

        $designation->update($formField);
        return $designation;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(designation $designation)
    {
        $designation->delete();

        return response()->json(['message' => 'Designation deleted successfully!']);
    }
}

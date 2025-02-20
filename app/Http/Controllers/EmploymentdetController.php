<?php

namespace App\Http\Controllers;

use App\Models\Employmentdet;
use Illuminate\Http\Request;

class EmploymentdetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employmentdets = employmentdet::with('user')->get();
        return response()->json($employmentdets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
            'position' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'dateofemp' => 'nullable|date',
        ]);

        $employmentdet = employmentdet::create($request->all());

        return response()->json([
            'message' => 'Employment details added successfully.',
            'data' => $employmentdet
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employmentdet = employmentdet::with('user')->find($id);

        if (!$employmentdet) {
            return response()->json(['message' => 'Employment details not found'], 404);
        }

        return response()->json($employmentdet);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employmentdet = employmentdet::find($id);

        if (!$employmentdet) {
            return response()->json(['message' => 'Employment details not found'], 404);
        }

        $request->validate([
            'userid' => 'sometimes|exists:users,id',
            'position' => 'sometimes|string|max:255',
            'organization' => 'sometimes|string|max:255',
            'dateofemp' => 'sometimes|date',
        ]);

        $employmentdet->update($request->all());

        return response()->json([
            'message' => 'Employment details updated successfully.',
            'data' => $employmentdet
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employmentdet = employmentdet::find($id);

        if (!$employmentdet) {
            return response()->json(['message' => 'Employment details not found'], 404);
        }

        $employmentdet->delete();

        return response()->json(['message' => 'Employment details deleted successfully']);
    }
}

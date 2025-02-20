<?php

namespace App\Http\Controllers;

use App\Models\EmpFamily;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmpfamilyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $empFamilies = EmpFamily::all();
        return response()->json($empFamilies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|integer',
            'children' => 'required|array',
            'children.*.name' => 'required|string',
            'children.*.dateofbirth' => 'required|date',
            'children.*.career' => 'nullable|string',
        ]);

        foreach ($request->children as $child) {
            Empfamily::create([
                'userid' => $request->userid,
                'children' => $child['name'], // Correct field
                'dateofbirth' => $child['dateofbirth'],
                'career' => $child['career'],
            ]);
        }

        return response()->json(['message' => 'Children saved successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $empFamily = EmpFamily::findOrFail($id);
        return response()->json($empFamily);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'userid' => 'required|integer',
            'children' => 'required|array',
            'children.*.name' => 'required|string',
            'children.*.dateofbirth' => 'required|date',
            'children.*.career' => 'nullable|string',
        ]);

        $empFamily = EmpFamily::findOrFail($id);
        $empFamily->update([
            'userid' => $validated['userid'],
            'children' => json_encode($validated['children']), // Store as JSON
        ]);

        return response()->json($empFamily);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $empFamily = EmpFamily::findOrFail($id);
        $empFamily->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }
}

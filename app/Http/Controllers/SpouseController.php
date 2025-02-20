<?php

namespace App\Http\Controllers;

use App\Models\Spouse;
use Illuminate\Http\Request;

class SpouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Spouse::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
            'name' => 'required|string',
            'dateofmarriage' => 'nullable|date',
        ]);

        $spouse = Spouse::create($request->only(['userid', 'name', 'dateofmarriage']));

        return response()->json($spouse, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $spouse = Spouse::find($id);
        
        if (!$spouse) {
            return response()->json(['message' => 'Spouse not found'], 404);
        }

        return response()->json($spouse, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $spouse = Spouse::find($id);
        
        if (!$spouse) {
            return response()->json(['message' => 'Spouse not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'dateofmarriage' => 'sometimes|date',
        ]);

        $spouse->update($request->only(['name', 'dateofmarriage']));

        return response()->json($spouse, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $spouse = Spouse::find($id);
        
        if (!$spouse) {
            return response()->json(['message' => 'Spouse not found'], 404);
        }

        $spouse->delete();

        return response()->json(['message' => 'Spouse deleted successfully'], 200);
    }
}

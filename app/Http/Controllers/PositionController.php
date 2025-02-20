<?php

namespace App\Http\Controllers;

use App\Models\position;
use App\Http\Requests\StorepositionRequest;
use App\Http\Requests\UpdatepositionRequest;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $position = position::all();
        return response()->json($position);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $pos = position::create([
            'name' => $request->name,
            'salary' => $request->salary
        ]);


        // Return the created leave type
        return response()->json($pos, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(position $position)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, position $position)
    {

        $formField = $request->validate([
            'name' => 'required|max:255',
            'salary' => 'required|max:255',
        ]);

        $position->update($formField);
        return $position;
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(position $position)
    {
        $position->delete();

        return response()->json(['message' => 'position deleted successfully!']);
    }
}

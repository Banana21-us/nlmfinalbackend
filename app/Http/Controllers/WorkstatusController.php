<?php

namespace App\Http\Controllers;

use App\Models\workstatus;
use App\Http\Requests\StoreworkstatusRequest;
use App\Http\Requests\UpdateworkstatusRequest;
use Illuminate\Http\Request;
class WorkstatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workstatus = workstatus::orderBy('name', 'asc')->get();
        return response()->json($workstatus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $workstatus = workstatus::create([
            'name' => $request->name,
        ]);
        return response()->json($workstatus, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(workstatus $workstatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, workstatus $workstatus)
    {
        $formField = $request->validate([
            'name' => 'required|max:255',
        ]);

        $workstatus->update($formField);
        return $workstatus;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(workstatus $workstatus)
    {
        $workstatus->delete();

        return response()->json(['message' => 'workstatus deleted successfully!']);
    }
}

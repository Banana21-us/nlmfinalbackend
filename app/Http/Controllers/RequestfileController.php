<?php

namespace App\Http\Controllers;

use App\Models\Requestfile;
use App\Models\User;
use App\Http\Requests\StoreRequestfileRequest;
use App\Http\Requests\UpdateRequestfileRequest;
use Illuminate\Http\Request;

class RequestfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() 
    {
        $users = User::leftJoin('requestfiles', 'users.id', '=', 'requestfiles.userid')
            ->select('users.id as userid', 'users.name', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
            ->get()
            ->groupBy('name')
            ->map(function ($files, $name) {
                $userid = $files->first()->userid ?? null; // Get user ID even if no files exist
                
                return [
                    'userid' => $userid,
                    'name' => $name,
                    'files' => $files->whereNotNull('id')->map(function ($file) {
                        return collect($file)->except(['name', 'userid']);
                    })->values()
                ];
            });
    
        return response()->json($users);
    }
    




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
            'description' => 'required|string|max:255',
            'file' => 'required|string|max:5000',
        ]);

        $requestfile = Requestfile::create([
            'userid' => $request->input('userid'),
            'description' => $request->input('description'),
            'file' => $request->input('file'),
            'time' => now(),
        ]);

        return response()->json([
            'message' => 'Request file added successfully.',
            'data' => $requestfile
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($userid)
    {
        $requestfiles = Requestfile::where('userid', $userid)->get();

        if ($requestfiles->isEmpty()) {
            return response()->json(['message' => 'No request files found for this user'], 404);
        }

        return response()->json($requestfiles);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $requestfile = Requestfile::find($id);

        if (!$requestfile) {
            return response()->json(['message' => 'Request file not found'], 404);
        }

        $request->validate([
            'description' => 'sometimes|string|max:255',
            'file' => 'sometimes|string|max:5000',
        ]);

        $requestfile->update($request->only(['description', 'file']));

        return response()->json($requestfile, 200);
    }

    public function destroy(Requestfile $requestfile)
    {
        $requestfile->delete();
        return response()->noContent();
    }
}

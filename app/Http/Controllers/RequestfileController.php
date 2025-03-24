<?php

namespace App\Http\Controllers;

use App\Models\Requestfile;
use App\Models\User;
use App\Http\Requests\StoreRequestfileRequest;
use App\Http\Requests\UpdateRequestfileRequest;
use Illuminate\Http\Request;
use App\Models\notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
class RequestfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function storeOrUpdateAccCode(Request $request, $userId)
    {
        // Validate the input
        $request->validate([
            'acc_code' => 'required|string|max:255|unique:users,acc_code,' . $userId,
        ]);

        // Find the user
        $user = User::findOrFail($userId);

        // Store or update the acc_code
        if (is_null($user->acc_code)) {
            $message = 'Account code stored successfully.';
        } else {
            $message = 'Account code updated successfully.';
        }

        $user->acc_code = $request->acc_code;
        $user->save();

        return response()->json(['message' => $message, 'acc_code' => $user->acc_code]);
    }


    public function uploadFiles(Request $request)
    {
        // Validate request
        $request->validate([
            'files.*' => 'required|file|max:2048' 
        ]);
    
        $uploadedFiles = $request->file('files');
        $savedFiles = [];
    
        foreach ($uploadedFiles as $file) {
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); 
    
            // Find user by acc_code instead of id
            $user = User::where('acc_code', $filename)->first(); 
    
            if ($user) {
                $destinationPath = public_path("storage/uploads/user_{$user->acc_code}");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true); 
                }
                $uniqueFileName = now()->format('m-d-Y') . '_' . $file->getClientOriginalName();
                $filePath = "storage/uploads/user_{$user->acc_code}/" . $uniqueFileName;
                   
                $file->move($destinationPath, $uniqueFileName);
    
                $fileUrl = asset($filePath);
    
                RequestFile::create([
                    'userid' => $user->id, 
                    'description' => 'Statement of Account',
                    'file' => "<p><a href='$fileUrl'>$uniqueFileName</a></p>",
                    'time' => Carbon::now()
                ]);
    
                $savedFiles[] = [
                    'acc_code' => $user->acc_code, 
                    'file_name' => $uniqueFileName,
                    'file_url' => $fileUrl,
                ];
            }
        }
    
        return response()->json([
            'message' => 'Files uploaded successfully!',
            'uploaded_files' => $savedFiles
        ]);
    }
    
     



    // public function index() 
    // {
    //     $users = User::leftJoin('requestfiles', 'users.id', '=', 'requestfiles.userid')
    //         ->select('users.id as userid', 'users.name', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
    //         ->get()
    //         ->groupBy('name')
    //         ->map(function ($files, $name) {
    //             $userid = $files->first()->userid ?? null; // Get user ID even if no files exist
                
    //             return [
    //                 'userid' => $userid,
    //                 'name' => $name,
    //                 'files' => $files->whereNotNull('id')->map(function ($file) {
    //                     return collect($file)->except(['name', 'userid']);
    //                 })->values()
    //             ];
    //         });
    
    //     return response()->json($users);
    // }

    public function bysoa() 
    {
        $users = User::leftJoin('requestfiles', 'users.id', '=', 'requestfiles.userid')
            ->select('users.id as userid', 'users.name', 'users.acc_code', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
            ->get()
            ->groupBy('name')
            ->map(function ($files, $name) {
                $userid = $files->first()->userid ?? null; // Get user ID even if no files exist
                $acc_code = $files->first()->acc_code ?? null; 

                $matchingFiles = $files->whereNotNull('id')
                    ->where('description', '=', 'Statement of Account')
                    ->map(function ($file) {
                        return collect($file)->except(['name', 'userid','acc_code']);
                    })->values();
                
                if ($matchingFiles->isEmpty()) {
                    return [
                        'userid' => $userid,
                        'acc_code' => $acc_code,
                        'name' => $name,
                        'message' => 'No request files found for this user',
                        'files' => []
                    ];
                } else {
                    return [
                        'userid' => $userid,
                        'acc_code' => $acc_code,
                        'name' => $name,
                        'files' => $matchingFiles
                    ];
                }
            });
        
        return response()->json($users);
    }

    public function bynotsoa() 
    {
        $users = User::leftJoin('requestfiles', 'users.id', '=', 'requestfiles.userid')
            ->select('users.id as userid', 'users.name', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
            ->get()
            ->groupBy('name')
            ->map(function ($files, $name) {
                $userid = $files->first()->userid ?? null; // Get user ID even if no files exist
                
                $matchingFiles = $files->whereNotNull('id')
                    ->where('description', '!=', 'Statement of Account')
                    ->map(function ($file) {
                        return collect($file)->except(['name', 'userid']);
                    })->values();
                
                if ($matchingFiles->isEmpty()) {
                    return [
                        'userid' => $userid,
                        'name' => $name,
                        'message' => 'No request files found for this user',
                        'files' => []
                    ];
                } else {
                    return [
                        'userid' => $userid,
                        'name' => $name,
                        'files' => $matchingFiles
                    ];
                }
            });
        
        return response()->json($users);
    }



    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'userid' => 'required|exists:users,id',
    //         'description' => 'required|string|max:255',
    //         'file' => 'required|string|max:5000',
    //     ]);

    //     $requestfile = Requestfile::create([
    //         'userid' => $request->input('userid'),
    //         'description' => $request->input('description'),
    //         'file' => $request->input('file'),
    //         'time' => now(),
    //     ]);

    //     return response()->json([
    //         'message' => 'Request file added successfully.',
    //         'data' => $requestfile
    //     ], 201);
    // }
    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
            'description' => 'required|string|max:255',
            'file' => 'required|string|max:5000',
        ]);
    
        // Create the request file
        $requestfile = Requestfile::create([
            'userid' => $request->input('userid'),
            'description' => $request->input('description'),
            'file' => $request->input('file'),
            'time' => now(),
        ]);
    
        // Create a notification for the user
        notification::create([
            'userid' => $request->input('userid'),
            'message' => 'Your ' . $request->input('description') . ' has been posted.',
            'type' => $request->input('description'),
            'is_read' => 0, // Mark as unread
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return response()->json([
            'message' => 'Request file added successfully, and notification sent.',
            'data' => $requestfile
        ], 201);
    }
    
    /**
     * Display the specified resource.
     */
    public function getrecordsByUserId($userid) 
    {
        $user = User::leftJoin('requestfiles', 'users.id', '=', 'requestfiles.userid')
            ->select('users.id as userid', 'users.name', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
            ->where('users.id', $userid)
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
        
        return response()->json($user);
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

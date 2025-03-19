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

     public function uploadFiles(Request $request)
     {
         // Validate request
         $request->validate([
             'files.*' => 'required|file|max:2048' // Validate files, no need to validate 'description'
         ]);
     
         $uploadedFiles = $request->file('files');
         $savedFiles = [];
     
         foreach ($uploadedFiles as $file) {
             $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // Extract filename without extension
     
             // Check if filename matches a user ID
             $user = User::find($filename);
     
             if ($user) {
                 // Ensure the directory exists
                 $destinationPath = public_path("storage/uploads/user_{$user->id}");
                 if (!file_exists($destinationPath)) {
                     mkdir($destinationPath, 0777, true); // Create directory if not exists
                 }
     
                 // Generate a unique file name to prevent overwriting
                 $uniqueFileName = now()->format('m-d-Y') . '_' . $file->getClientOriginalName();
                 $filePath = "storage/uploads/user_{$user->id}/" . $uniqueFileName;
                    
                 // Move file to public storage directory
                 $file->move($destinationPath, $uniqueFileName);
     
                 // Generate a public URL for the file
                 $fileUrl = asset($filePath);
     
                 // Save to database with a clickable link
                 RequestFile::create([
                     'userid' => $user->id,
                     'description' => 'Statement of Account',
                     'file' => "<p><a href='$fileUrl'>$uniqueFileName</a></p>",
                     'time' => Carbon::now()
                 ]);
     
                 $savedFiles[] = [
                     'user_id' => $user->id,
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
            ->select('users.id as userid', 'users.name', 'requestfiles.id', 'requestfiles.description', 'requestfiles.file', 'requestfiles.time')
            ->get()
            ->groupBy('name')
            ->map(function ($files, $name) {
                $userid = $files->first()->userid ?? null; // Get user ID even if no files exist
                
                $matchingFiles = $files->whereNotNull('id')
                    ->where('description', '=', 'Statement of Account')
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

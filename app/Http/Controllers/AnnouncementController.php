<?php

namespace App\Http\Controllers;

use App\Models\notification;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::all();
        return response()->json($announcements);
    }

    /**
     * Store a newly created resource in storage.
     */

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'announcement' => 'required|string|max:5000',
    ]);

    Log::info('Creating a new announcement', ['title' => $request->title]);

    $announcement = Announcement::create([
        'title' => $request->title,
        'announcement' => $request->announcement,
    ]);

    // Retrieve all users where reg_approval is NOT NULL
    $users = User::whereNotNull('reg_approval')->get();

    if ($users->isEmpty()) {
        Log::warning('No users found with reg_approval');
        return response()->json(['message' => 'No users found'], 404);
    }

    foreach ($users as $user) {
        // Check if a notification already exists for this user
        $exists = notification::where('userid', $user->id)
            ->where('message', "New announcement posted: \n{$announcement->title}")
            ->exists();

        if (!$exists) {
            // Create a notification
            notification::create([
                'userid' => $user->id,
                'type' => "Announcements",
                'message' => "New announcement posted: \n{$announcement->title}",
                'is_read' => false
            ]);

            Log::info("Notification created for user", ['user_id' => $user->id]);
        } else {
            Log::info("Notification already exists for user", ['user_id' => $user->id]);
        }
    }

    Log::info("Announcement process completed successfully", ['announcement_id' => $announcement->id]);

    return response()->json(['message' => 'Announcement created and notifications sent']);
}




    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'announcement' => 'sometimes|required|string',
        ]);

        $announcement->update($request->only(['title', 'announcement']));

        return response()->json([
            'message' => 'Announcement updated successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully'
        ]);
    }
}

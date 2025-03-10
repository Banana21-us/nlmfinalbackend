<?php

namespace App\Http\Controllers;

use App\Models\notification;
use App\Models\User;
use Illuminate\Http\Request;    
use App\Http\Requests\StorenotificationRequest;
use App\Http\Requests\UpdatenotificationRequest;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        return response()->json(notification::all());
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $notification = notification::create($request->all());

        return response()->json(['message' => 'notification created', 'notification' => $notification], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(notification $notification)
    {
        return response()->json($notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, notification $id)
    {
        $id->update(['is_read' => true]);
        
        return response()->json(['message' => 'Notification marked as read']);
    }
    public function getUnreadNotificationCount($userId) {
        $count = notification::where('userid', $userId)
                             ->where('is_read', false)
                             ->count();
    
        return response()->json(['count' => $count]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, notification $id)
    {
        $id->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}

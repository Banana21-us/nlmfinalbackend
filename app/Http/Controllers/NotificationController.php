<?php

namespace App\Http\Controllers;

use App\Models\notification;
use App\Models\User;
use Illuminate\Http\Request;    
use App\Http\Requests\StorenotificationRequest;
use App\Http\Requests\UpdatenotificationRequest;
use Illuminate\Support\Facades\Log;
class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function getnotif($userId)
{
    if (!$userId) {
        return response()->json(['message' => 'User ID is required'], 400);
    }

    \Log::info('Fetching notifications for user ID:', ['userId' => $userId]);

    $notifications = notification::where('userid', $userId)
        ->whereIn('type', ['User Request', 'Announcements','Statement of Account','Service Records','Leave Request','Leave Approval'])
        ->orderBy('created_at', 'desc')
        ->get();
    \Log::info('Sorted notifications:', ['notifications' => $notifications]);
    \Log::info('Notifications fetched:', ['notifications' => $notifications]);

    return response()->json([
        'user_requests' => $notifications->where('type', 'User Request')->values(),
        'announcements' => $notifications->where('type', 'Announcements')->values(),
        'statementofaccouunt' => $notifications->where('type', 'Statement of Account')->values(),
        'servicerecords' => $notifications->where('type', 'Service Records')->values(),
        'leavereq' => $notifications->where('type', 'Leave Request')->values(),
        'leaveapproval'=> $notifications->where('type', 'Leave Approval')->values(),

    ]);
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

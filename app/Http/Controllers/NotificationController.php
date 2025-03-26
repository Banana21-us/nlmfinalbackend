<?php

namespace App\Http\Controllers;

use App\Models\notification;
use App\Models\User;
use App\Models\events;
use Illuminate\Http\Request;    
use App\Http\Requests\StorenotificationRequest;
use App\Http\Requests\UpdatenotificationRequest;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function getnotif($userId)
    {
        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }
    
        \Log::info('Fetching notifications for user ID:', ['userId' => $userId]);
    
        // Fetch notifications for the user
        $notifications = notification::where('userid', $userId)
            ->whereIn('type', ['User Request', 'Announcements', 'Statement of Account', 'Service Records', 'Leave Request', 'Leave Approval'])
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Fetch today's events for the user
        $today = now()->toDateString(); // Get current date in 'YYYY-MM-DD' format
        $events = events::where('userid', $userId)
            ->whereDate('time', $today)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'userid' => $event->userid,
                    'message' => "Upcoming Event: " . $event->title . " at " . Carbon::parse($event->time)->format('F j, Y h:i A'),
                    'type' => 'Event',
                    'is_read' => 0,
                    'created_at' => $event->created_at,
                ];
            });
    
        \Log::info('Sorted notifications:', ['notifications' => $notifications]);
        \Log::info('Fetched today\'s events:', ['events' => $events]);
    
        return response()->json([
            'user_requests' => $notifications->where('type', 'User Request')->values(),
            'announcements' => $notifications->where('type', 'Announcements')->values(),
            'statementofaccount' => $notifications->where('type', 'Statement of Account')->values(),
            'servicerecords' => $notifications->where('type', 'Service Records')->values(),
            'leavereq' => $notifications->where('type', 'Leave Request')->values(),
            'leaveapproval' => $notifications->where('type', 'Leave Approval')->values(),
            'events' => $events->values(),
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

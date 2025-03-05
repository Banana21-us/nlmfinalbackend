<?php

namespace App\Http\Controllers;

use App\Models\events;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Events::all();
        return response()->json($events);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    Log::info('Store method called', ['request' => $request->all()]);

    $request->validate([
        'title' => 'required|string|max:255',
        'time' => 'required|date_format:Y-m-d\TH:i', // Adjusted format
        'userid' => 'required|exists:users,id',
    ]);

    // Convert time if necessary
    $time = Carbon::createFromFormat('Y-m-d\TH:i', $request->time)->format('Y-m-d H:i:s');

    Log::info('Validation passed');

    $event = events::create([
        'title' => $request->title,
        'time' => $time,
        'userid' => $request->userid,
    ]);

    Log::info('Event created', ['event' => $event]);
    return response()->json($event, 201);
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $event = events::find($id);
        // where('id', $id)->where('userid', Auth::id())->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event);

    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Update method called', ['id' => $id, 'request' => $request->all()]);

        $event = events::findOrFail($id);
        // where('id', $id)->where('userid', Auth::id())->first();

        if (!$event) {
            Log::warning('Event not found', ['id' => $id]);
            return response()->json(['message' => 'Event not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'time' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        Log::info('Validation passed');

        $event->update($request->only(['title', 'time']));

        Log::info('Event updated', ['event' => $event]);

        return response()->json(['message' => 'Event updated successfully', 'event' => $event]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $event = events::where('id', $id)->where('userid', Auth::id())->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}

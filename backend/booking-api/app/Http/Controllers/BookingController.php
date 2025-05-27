<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $user_id = Auth::user()->id;

        $bookings = Booking::where('user_id', $user_id)->with('service')->get();

        if ($bookings->isEmpty()) {
            return response()->json(['error' => 'No bookings found'], 404);
        }

        return response()->json($bookings);
    }

public function show($id)
{
    $user_id = Auth::id();

    $booking = Booking::where('id', $id)
                      ->where('user_id', $user_id)
                      ->with('service') // Make sure service relationship exists in Booking model
                      ->first();

    if (! $booking) {
        return response()->json([
            'error' => 'Booking not found or unauthorized access.'
        ], 404);
    }

    return response()->json($booking, 200);
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'opening_hours' => 'required',
        ]);

if ($validator->fails()) {
    return response()->json([
        'message' => 'Validation failed.',
        'errors' => $validator->errors()
    ], 422);
}


        $user_id = Auth::id();
        $booking = new Booking();  
        $booking->service_id = $request->service_id;
        $booking->opening_hours = $request->opening_hours;
        $booking->user_id = $user_id;
        $booking->save();

        return response()->json([
            'message' => 'Booking added successfully!',
            'booking' => $booking,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'service_id' => 'sometimes|exists:services,id',
            'opening_hours' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->fill($request->only(['service_id', 'opening_hours']));
        $booking->save();

        return response()->json([
            'message' => 'Booking updated successfully.',
            'booking' => $booking
        ], 200);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully!',
            'booking' => $booking
        ], 200);
    }
}

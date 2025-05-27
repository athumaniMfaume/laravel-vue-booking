<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Business;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::all();

        if ($reviews->isEmpty()) {
            return response()->json(['error' => 'No reviews found'], 404);
        }

        return response()->json($reviews);
    }

public function show($id)
{
    $user_id = Auth::id();
    $review = Review::where('id', $id)->where('user_id', $user_id)->first();

    if (! $review) {
        return response()->json(['error' => 'Review not found or you do not have access'], 404);
    }

    return response()->json($review);
}


    public function business_review($id)
    {
        $validator = Validator::make(['business_id' => $id], [
            'business_id' => 'required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid business ID.'], 400);
        }

        $reviews = Review::where('business_id', $id)->get();

        if ($reviews->isEmpty()) {
            return response()->json(['error' => 'No reviews found'], 404);
        }

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,id',
            'review' => 'required|string|max:1000',
            'stars' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $review = new Review();
        $review->business_id = $request->business_id;
        $review->review = $request->review;
        $review->stars = $request->stars;
        $review->user_id = Auth::id();
        $review->save();

        return response()->json([
            'message' => 'Review added successfully!',
            'review' => $review,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);
            $user_id = Auth::id();

    if (!$review || $review->user_id !== $user_id) {
        return response()->json(['error' => 'Review not found or you do not have access'], 404);
    }



        $validator = Validator::make($request->all(), [
            'business_id' => 'sometimes|exists:business,id',
            'review' => 'sometimes|string|max:1000',
            'stars' => 'sometimes|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $review->fill($request->only(['business_id', 'review', 'stars']));
        $review->save();

        return response()->json([
            'message' => 'Review updated successfully!',
            'review' => $review
        ], 200);
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (! $review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully!',
            'review' => $review
        ], 200);
    }
}

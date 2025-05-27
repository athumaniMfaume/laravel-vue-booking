<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    // GET /admin/businesses
    public function index()
    {
        $businesses = Business::with('user')->get();

            if (!$businesses) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

        return response()->json([
            'businesses' => $businesses
        ]);
    }

public function user()
{
    $user = auth()->user();
    $businesses = Business::all(); // without 'with()'

    return response()->json([
        'businesses' => $businesses
    ]);
}    

    // GET /admin/businesses/{id}
    public function show($id)
    {
        $business = Business::with('user')->find($id);

        if (!$business) {
            return response()->json(['message' => 'Business not found.'], 404);
        }

        return response()->json(['business' => $business]);
    }

    // POST /admin/businesses
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'user_id'        => 'required|exists:users,id',
            'opening_hours'  => 'required|string|max:255',
            'status'         => 'required|in:open,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $business = Business::create($request->only('name', 'user_id', 'opening_hours', 'status'));

        return response()->json([
            'message'  => 'Business added successfully.',
            'business' => $business
        ]);
    }

    // PUT /admin/businesses/{id}
    public function update(Request $request, $id)
    {
        $business = Business::find($id);

        if (!$business) {
            return response()->json(['message' => 'Business not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'user_id'        => 'required|exists:users,id',
            'opening_hours'  => 'required|string|max:255',
            'status'         => 'required|in:open,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $business->update($request->only('name', 'user_id', 'opening_hours', 'status'));

        return response()->json([
            'message'  => 'Business updated successfully.',
            'business' => $business
        ]);
    }

    // DELETE /admin/businesses/{id}
    public function destroy($id)
    {
        $business = Business::find($id);

        if (!$business) {
            return response()->json(['message' => 'Business not found.'], 404);
        }

        $business->delete();

        return response()->json(['message' => 'Business deleted successfully.']);
    }
}

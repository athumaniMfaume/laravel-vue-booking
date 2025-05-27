<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $business = Business::where('user_id', $user->id)->first();

        if (! $business) {
            return response()->json([
                'error' => 'This user has no business yet.'
            ], 404);
        }

        $services = Service::where('business_id', $business->id)->get();

        if ($services->isEmpty()) {
            return response()->json([
                'error' => 'No services found for this business.'
            ], 404);
        }

        return response()->json(['services' => $services], 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();

        if (! $business) {
            return response()->json(['error' => 'User does not have a business.'], 404);
        }

        $service = Service::where('business_id', $business->id)->where('id', $id)->first();

        if (! $service) {
            return response()->json(['error' => 'Service not found.'], 404);
        }

        return response()->json(['service' => $service], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user_id = Auth::id();
        $business = Business::where('user_id', $user_id)->first();

        if (! $business) {
            return response()->json([
                'error' => 'This user does not have a business.'
            ], 404);
        }

        $service = new Service();
        $service->name = $request->name;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->business_id = $business->id;
        $service->save();

        return response()->json([
            'message' => 'Service added successfully!',
            'service' => $service,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();

        if (! $business) {
            return response()->json(['error' => 'User does not have a business.'], 404);
        }

        $service = Service::where('business_id', $business->id)->where('id', $id)->first();

        if (! $service) {
            return response()->json(['error' => 'Service not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price'       => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $service->name = $request->name;
        }

        if ($request->has('description')) {
            $service->description = $request->description;
        }

        if ($request->has('price')) {
            $service->price = $request->price;
        }

        $service->save();

        return response()->json([
            'message' => 'Service updated successfully!',
            'service' => $service
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();

        if (! $business) {
            return response()->json(['error' => 'User does not have a business.'], 404);
        }

        $service = Service::where('business_id', $business->id)->where('id', $id)->first();

        if (! $service) {
            return response()->json(['error' => 'Service not found.'], 404);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully!',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;

class ServicesController extends Controller
{
    public function createService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'base_fare' => 'required',
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $service = new Service([
                'name' => $request->input('name'),
                'base_fare' => $request->input('base_fare'),
                'image' => $request->input('image'),
                'status' => 1,
            ]);

            $service->save();

            return response()->json(['message' => 'Service created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getService()
    {
        try {
            $service = Service::where('status', 1)->get();
            return response()->json(['services' => $service], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        if ($service->status == 1) {
            $service->status = 0;
            $service->save();
            return response()->json(['message' => 'Service deleted successfully', 'status' => $service->status]);
        } else {
            return response()->json(['message' => 'Service is already deleted', 'status' => $service->status]);
        }
    }
}

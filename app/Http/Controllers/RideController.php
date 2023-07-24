<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ride;
use App\Models\Card;


class RideController extends Controller
{
    public function createRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pick_up'  => 'required',
            'drop_of'  => 'required',
            'no_of_passenger'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ride = new Ride([
                'pick_up' => $request->input('pick_up'),
                'drop_of' => $request->input('drop_of'),
                'no_of_passenger' => $request->input('no_of_passenger'),
                'distance' => $request->input('distance'),
                'estimated_time' => $request->input('estimated_time'),
                'estimated_fare' => $request->input('estimated_fare'),
                'accept_time' => $request->input('accept_time'),
                'code' => $request->input('code'),
                'service_id' => $request->input('service_id'),
                'card_id' => $request->input('card_id'),
                'accept_driver_id' => $request->input('accept_driver_id'),
            ]);

            // $ride->service()->associate($request->input('service_id'));
            // $ride->card()->associate($request->input('card_id'));
            // $ride->user()->associate($request->input('accept_driver_id'));


            $ride->save();

            return response()->json(['message' => 'Ride created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

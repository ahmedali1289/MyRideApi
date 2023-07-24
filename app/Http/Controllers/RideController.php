<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ride;
use App\Models\Card;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;



class RideController extends Controller
{
    public function createRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pick_up'  => 'required',
            'drop_of'  => 'required',
            'no_of_passenger'  => 'required',
            'service_id' => 'required|exists:services,id',
            'card_id' => 'required|exists:cards,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $userId = auth()->user()->id;
    
            $card = Card::find($request->input('card_id'));
            $service = Service::find($request->input('service_id'));
    
            if (!$card) {
                return response()->json(['error' => 'Card not found'], 404);
            }
    
            $userRole = auth()->user()->role;
    
            $acceptDriverId = null;
            if ($userRole === 'driver') {
                $acceptDriverId = $userId;
            } else {
                $acceptDriver = User::where('id', $request->input('accept_driver_id'))->where('role', 'driver')->first();
                if (!$acceptDriver) {
                    return response()->json(['error' => 'Driver not found'], 404);
                }
                $acceptDriverId = $acceptDriver->id;
            }
    
            $ride = new Ride([
                'user_id' => $userId,
                'pick_up' => $request->input('pick_up'),
                'drop_of' => $request->input('drop_of'),
                'no_of_passenger' => $request->input('no_of_passenger'),
                'distance' => $request->input('distance'),
                'estimated_time' => $request->input('estimated_time'),
                'estimated_fare' => $request->input('estimated_fare'),
                'accept_time' => $request->input('accept_time'),
                'code' => $request->input('code'),
                'service_id' =>  $service->id,
                'card_id' => $card->id,
                'accept_driver_id' => $acceptDriverId,
            ]);
    
            $ride->save();
    
            return response()->json(['message' => 'Ride created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    

    public function getRide()
    {
        try {
            $userId = auth()->user()->id;
            $ride = Ride::latest('created_at')->get();
    
            if ($ride) {
                return response()->json(['Rides' => $ride], 200);
            } else {
                return response()->json(['message' => 'Ride not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

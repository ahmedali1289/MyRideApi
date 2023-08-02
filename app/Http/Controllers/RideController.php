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
                return response()->json(['message' => 'Card not found'], 404);
            }
    
            $userRole = auth()->user()->role;
    
            // $acceptDriverId = null;
            // if ($userRole === 'driver') {
            //     $acceptDriverId = $userId;
            // } else {
            //     $acceptDriver = User::where('id', $request->input('accept_driver_id'))->where('role', 'driver')->first();
            //     if (!$acceptDriver) {
            //         return response()->json(['message' => 'Driver not found'], 404);
            //     }
            //     $acceptDriverId = $acceptDriver->id;
            // }
    
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
                'status' => 1,
                // 'accept_driver_id' => $acceptDriverId,
            ]);
    
            $ride->save();
    
            return response()->json(['message' => 'Ride created successfully', 'Ride ' => $ride], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'message', 'message' => $e->getMessage()]);
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
            return response()->json(['status' => 'message', 'message' => $e->getMessage()], 500);
        }
    }
    public function getDriverRideRequests()
    {
        try {
            $userId = auth()->user()->id;
            $latestRides = Ride::select('user_id', \DB::raw('MAX(created_at) as latest_created_at'))
                ->where('accept_driver_id', null)
                ->groupBy('user_id')
                ->get();
            $userIds = $latestRides->pluck('user_id')->toArray();
            $latestTimestamps = $latestRides->pluck('latest_created_at')->toArray();
            $rides = Ride::whereIn('user_id', $userIds)
                ->whereIn('created_at', $latestTimestamps)
                ->where('status', 1)
                ->with('user')
                ->get();

            if ($rides) {
                return response()->json(['Rides' => $rides], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function updateRideStatusToZero(Request $request)
    {
        try {
            $id = $request->input('id');
            $ride = Ride::where('id', $id)->where('status', 1)->first();
            if ($ride) {
                $ride->status = 0;
                $ride->save();
                return response()->json(['message' => 'Ride status updated to zero'], 200);
            } else {
                return response()->json(['message' => 'Ride not found or status is already zero'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

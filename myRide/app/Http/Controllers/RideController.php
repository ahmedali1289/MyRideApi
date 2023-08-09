<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ride;
use App\Models\Card;
use App\Models\Service;
use App\Models\User;
use App\Models\DriverDocument;
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
    
            return response()->json(['message' => 'Searched ride successfully', 'Ride' => $ride], 201);
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
            $user = auth()->user();
            if ($user->role !== 'driver') {
                return response()->json(['status' => 'error', 'message' => 'Role access denied'], 403);
            }
            
            $driverDocuments = $user->documents;
            
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
                ->get()
                ->filter(function ($ride) use ($driverDocuments) {
                    return $ride->no_of_passenger <= $driverDocuments->car_capacity
                        && $ride->service_id == $driverDocuments->service;
                });
    
            if ($rides->isEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'No suitable ride requests available'], 200);
            }
            
            return response()->json(['Rides' => $rides], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function driverAcceptedRides(Request $request)
    {
        try {
            $userId = auth()->user()->id;
    
            $latestRide = Ride::where('accept_driver_id', '<>', null)
                ->whereIn('status', [0,1])
                ->where('user_id', $userId)
                ->with('user')
                ->with('service')
                ->with('driver.documents')
                ->latest('created_at')
                ->first();
    
            if (!$latestRide) {
                return response()->json(['message' => 'No rides found'], 404);
            }
            return response()->json(['Ride' => $latestRide], 200);
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
    public function acceptRideDriver(Request $request)
    {
        try {
            $id = $request->input('id');
            $acceptDriverId = $request->input('driver_id');
            
            $ride = Ride::where('id', $id)
                ->whereIn('status', [0, 1])
                ->whereNull('accept_driver_id')
                ->first();
            
            if ($ride) {
                $ride->accept_driver_id = $acceptDriverId;
                $ride->save();
                return response()->json(['message' => 'Ride accepted'], 200);
            } else {
                return response()->json(['message' => 'Ride not found or already accepted'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function cancelRideByPassenger(Request $request)
    {
        try {
            $id = $request->input('id');
            $user_id = $request->input('user_id');
            
            $ride = Ride::where('id', $id)
                ->where('user_id', $user_id)
                ->whereIn('status', [0,1])
                ->whereNotNull('accept_driver_id')
                ->first();
            
            if ($ride) {
                if ($ride->status == 2 && $ride->cancelled_by == $user_id) {
                    return response()->json(['message' => 'Ride already cancelled'], 400);
                }
                
                $createdAt = $ride->created_at;
                $currentTime = now();
                $timeDifference = $currentTime->diffInMinutes($createdAt);
                
                if ($timeDifference <= 2) {
                    $ride->status = 2;
                    $ride->cancelled_by = $user_id;
                    $ride->save();
                    
                    Ride::where('user_id', $user_id)
                        ->whereIn('status', [0,1])
                        ->where('created_at', '<', $currentTime)
                        ->update(['status' => 2, 'cancelled_by' => $user_id]);
                        
                    return response()->json(['message' => 'Ride cancelled successfully'], 200);
                } else {
                    return response()->json(['message' => 'Ride cannot be cancelled now'], 400);
                }
            } else {
                return response()->json(['message' => 'Ride not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

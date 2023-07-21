<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Rating;
use App\Models\DriverDocument;


use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function createCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'card_no' => 'required',
            'date' => 'required',
            'ccv' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 403);
        }

        try {
            $userId = auth()->user()->id;
    
            $cart = new Cart([
                'user_id' => $userId,
                'name' => $request->input('name'),
                'card_no' => $request->input('card_no'),
                'date' => $request->input('date'),
                'ccv' => $request->input('ccv'),
                'status' => 1,
            ]);
    
            $cart->save();
            
            return response()->json(['message' => 'Cart created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if ($cart->status == 1) {
            $cart->status = 0;
            $cart->save();
            return response()->json(['message' => 'Cart deleted successfully', 'status' => $cart->status]);
        } else {
            return response()->json(['message' => 'Cart is already deleted', 'status' => $cart->status]);
        }
    }

    public function get()
    {
        try {
            $user = Auth::user();
            $cart = Cart::where('status', 1)->get();
            return response()->json(['carts' => $cart], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }



    public function createRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_document_id' => 'required|exists:driver_documents,id',
            'stars' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 403);
        }

        try {
            $userId = auth()->user()->id;
            $driverDocumentId = $request->input('driver_document_id');
            $driverDocument = DriverDocument::where('user_id', $userId)->find($driverDocumentId);

            if (!$driverDocument) {
                return response()->json(['success' => false, 'message' => 'Driver document not found for the authenticated user'], 404);
            }

            $rating = new Rating([
                'user_id' => $userId,
                'document_id' => $driverDocumentId, // Use 'document_id' here, assuming the foreign key is named 'document_id'
                'stars' => $request->input('stars'),
                'description' => $request->input('description'),
            ]);

            $rating->save();

            return response()->json(['message' => 'Rating created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }



    
}

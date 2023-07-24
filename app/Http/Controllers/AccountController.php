<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;
use App\Models\Rating;
use App\Models\DriverDocument;


use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function createCard(Request $request)
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

            $existingCard = Card::where('name', $request->input('name'))
                ->where('card_no', $request->input('card_no'))
                ->where('date', $request->input('date'))
                ->where('ccv', $request->input('ccv'))
                ->where('user_id', $userId)
                ->first();

            if ($existingCard) {
                return response()->json(['message' => 'Card with the same details already exists for this user'], 200);
            }

            $card = new Card([
                'user_id' => $userId,
                'name' => $request->input('name'),
                'card_no' => $request->input('card_no'),
                'date' => $request->input('date'),
                'ccv' => $request->input('ccv'),
                'type' => $request->input('type'),
                'status' => 1,
            ]);

            $card->save();

            return response()->json(['message' => 'Card created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }



    public function delete(Request $request, $id)
    {
        $card = Card::find($id);

        if (!$card) {
            return response()->json(['message' => 'card not found'], 404);
        }

        if ($card->status == 1) {
            $card->status = 0;
            $card->save();
            return response()->json(['message' => 'card deleted successfully', 'status' => $card->status]);
        } else {
            return response()->json(['message' => 'card is already deleted', 'status' => $card->status]);
        }
    }

    public function get()
    {
        try {
            $user = Auth::user();
            $card = Card::where('status', 1)->get();
            return response()->json(['cards' => $card], 200);
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

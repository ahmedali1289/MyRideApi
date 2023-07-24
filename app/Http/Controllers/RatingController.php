<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\DriverDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    
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
                'document_id' => $driverDocumentId, 
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

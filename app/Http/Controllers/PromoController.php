<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PromoCode;

class PromoController extends Controller
{
    public function createCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_no' => 'required',
            'discount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $promo = new PromoCode([
                'coupon_no' => $request->input('coupon_no'),
                'discount' => $request->input('discount'),
                'status' => 1,
            ]);

            $promo->save();

            return response()->json(['message' => 'Promo Code created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCodes()
    {
        try {
            $promo = PromoCode::where('status', 1)->get();
            return response()->json(['Discount Codes' => $promo], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, $id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo code not found'], 404);
        }

        if ($promo->status == 1) {
            $promo->status = 0;
            $promo->save();
            return response()->json(['message' => 'Promo code deleted successfully', 'status' => $promo->status]);
        } else {
            return response()->json(['message' => 'Promo code is already deleted', 'status' => $promo->status]);
        }
    }
}

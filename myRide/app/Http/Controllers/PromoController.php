<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PromoCode;
use App\Models\User;

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
                'use_status' => 0,
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
            return response()->json(['codes' => $promo], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function coupanValid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_no' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $couponNo = $request->input('coupon_no');
            $promo = PromoCode::where('coupon_no', $couponNo)->where('status', 1)->first();

            if ($promo) {
                // Check if the user exists in the users table
                $user = User::find($request->input('user_id'));
                if (!$user) {
                    return response()->json(['message' => 'User does not exist'], 404);
                }

                // Check if the user has already availed the coupon
                $userIds = json_decode($promo->use_status, true);

                if (is_array($userIds) && in_array($request->input('user_id'), $userIds)) {
                    return response()->json(['message' => 'Coupon already availed by this user'], 400);
                }

                // Coupon exists and is active, update use_status
                $userIds = is_array($userIds) ? $userIds : []; // Ensure $userIds is an array
                $userIds[] = $request->input('user_id');
                $promo->use_status = json_encode($userIds);
                $promo->save();

                return response()->json(['message' => 'Promo Code applied successfully', 'promo' => $promo->discount], 200);
            } else {
                return response()->json(['message' => 'Invalid promo code or code is not active'], 404);
            }
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

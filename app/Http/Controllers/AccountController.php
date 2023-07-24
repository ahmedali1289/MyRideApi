<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;
use App\Models\Account;
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
            return response()->json(['errors' => $validator->errors()], 422);
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
            $card = Card::where('status', 1)
                ->where('user_id', $user->id)
                ->latest('created_at')->get();
    
            return response()->json(['card' => $card], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    


    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'account_no' => 'required',
            'branch_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        try {
            $userId = auth()->user()->id;

            $existingAccount = Account::where('name', $request->input('name'))
                ->where('account_no', $request->input('account_no'))
                ->where('branch_code', $request->input('branch_code'))
                ->where('user_id', $userId)
                ->first();

            if ($existingAccount) {
                return response()->json(['message' => 'Account with the same details already exists for this user'], 200);
            }

            $account = new Account([
                'user_id' => $userId,
                'name' => $request->input('name'),
                'account_no' => $request->input('account_no'),
                'branch_code' => $request->input('branch_code'),
                'status' => 1,
            ]);

            $account->save();

            return response()->json(['message' => 'Account created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getAccount()
    {
        try {
            $user = Auth::user();
            $account = Account::where('status', 1)->get();
            return response()->json(['accounts' => $account], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }





    
}

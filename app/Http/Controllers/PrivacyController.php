<?php

namespace App\Http\Controllers;

use Faker\Provider\Lorem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Privacy;
use App\Models\Term;
use App\Models\Help;
use App\Mail\HelpMail;
use Illuminate\Support\Facades\Mail;

class PrivacyController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $description = $request->input('description');
    
        try {
            $privacy = Privacy::first();
    
            if ($privacy) {
                $privacy->update([
                    'description' => $description
                ]);
            } else {
                Privacy::create([
                    'description' => $description
                ]);
            }
    
            return response()->json(['message' => 'Privacy  Created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getPrivacy()
    {
        try {
            $privacy = Privacy::first();
    
            if ($privacy) {
                return response()->json(['description' => $privacy->description], 200);
            } else {
                return response()->json(['message' => 'Privacy settings not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



    public function create_term(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $description = $request->input('description');
    
        try {
            $term = Term::first();
    
            if ($term) {
                $term->update([
                    'description' => $description
                ]);
            } else {
                Term::create([
                    'description' => $description
                ]);
            }
    
            return response()->json(['message' => 'Term Created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getTerm()
    {
        try {
            $term = Term::first();
    
            if ($term) {
                return response()->json(['description' => $term->description], 200);
            } else {
                return response()->json(['message' => 'Term settings not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function create_help(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $description = $request->input('description');

        try {
            $help = Help::first();

            if ($help) {
                $help->update([
                    'description' => $description
                ]);
            } else {
                $userId = auth()->user()->id;
                Help::create([
                    'user_id' => $userId,
                    'description' => $description
                ]);
            }

            $adminEmail = 'wahabasifdeveloper@gmail.com';
            Mail::to($adminEmail)->send(new HelpMail($description));

            return response()->json(['message' => 'Term Created successfully'], 200);
        } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

    
}

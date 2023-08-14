<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use App\Models\DriverDocument;
use App\Models\Requests;
use App\Mail\SendMail;
use App\Mail\SendPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function otpGenerate(Request $request)
    {
        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return response()->json(['error' => 'User already registered'], 409);
        }
        $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        Otp::updateOrCreate(
            ['email' => $request->input('email')],
            ['otp' => $otp]
        );
        try {
             $view = view('otpTemplate', compact('otp'))->render();
            $mailData = [
                'subject' => 'OTP Code',
                'to' => $request->email,
                'view' => $view
            ];
            $this->sendPasswordMail($mailData);
            // Mail::to($request->input('email'))->send(new SendMail($otp));
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }
    
    public function register(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $otp = $request->input('otp');
        $role = $request->input('role');
        $active_status;
        if($role == 'passenger' || $role == 'admin'){
            $otpData = Otp::where('email', $email)->first();
            if (!$otpData) {
                return response()->json(['error' => 'Email not found'], 404);
            }
            if ($otpData->otp !== $otp) {
                return response()->json(['error' => 'Invalid OTP'], 400);
            }
            if (strlen($password) < 8) {
                return response()->json(['error' => 'Password must be at least 8 characters long'], 400);
            }
            $active_status = 1;
            $password = Hash::make($password);
        }
        else{
            $active_status = 0;
            $password = null;
        }
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return response()->json(['error' => 'User already registered'], 409);
        }

        $user = User::create([
            'fname' => $request->input('fname'),
            'lname' => $request->input('lname'),
            'email' => $email,
            'password' => $password,
            'phone' => $request->input('phone'),
            'role' => $role,
            'active_status' => $active_status,
            'image' => 'https://canningsolicitors.ie/wp-content/uploads/2021/12/00-user-dummy-200x200-1.png',
        ]);
        $user_id = $user->id;
        if($role == 'driver'){
            $driver_details = DriverDocument::create([
                'user_id' => $user_id,
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'car_make' => $request->input('car_make'),
                'car_model' => $request->input('car_model'),
                'car_year' => $request->input('car_year'),
                'car_color' => $request->input('car_color'),
                'car_capacity' => $request->input('car_capacity'),
                'service' => $request->input('service'),
                'driver_liscence' => $request->input('driver_liscence'),
                'car_registration' => $request->input('car_registration'),
                'car_insurance' => $request->input('car_insurance'),
                'liscence_picture' => $request->input('liscence_picture'),
                'car_picture' => $request->input('car_picture'),
              ]);
              $driver_details = Requests::create([
                'user_id' => $user_id,
                'active_status' => $active_status,
                'request' => 0,
              ]);
        }
        if($role == 'driver'){
            return response()->json(['message' => 'Wait for admin approval'], 200);
        }
        else{
            return response()->json(['message' => 'User registered successfully'], 200);
        }
    }
    
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
                'role' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
    
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                if ($user->active_status == 1) {
                    if ($user->role !== $request->input('role')) {
                        // Driver access is denied
                        return response()->json(['error' => 'Unauthorized: Role access denied.'], 401);
                    }
    
                    $token = $user->createToken('MyApp')->accessToken;
                    return response()->json(['message' => 'Successfully logged in', 'access_token' => $token, 'user' => $user]);
                } else {
                    return response()->json(['error' => 'Your account is inactive.'], 403);
                }
            } else {
                return response()->json(['error' => 'Invalid email or password.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
    

    public function forgetPassword(Request $request)
    {
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        Otp::updateOrCreate(
            ['email' => $email],
            ['otp' => $otp]
        );

        try {
             $view = view('otpTemplate', compact('otp'))->render();
            $mailData = [
                'subject' => 'OTP Code',
                'to' => $request->email,
                'view' => $view
            ];
            $this->sendPasswordMail($mailData);
            // Mail::to($request->input('email'))->send(new SendMail($otp));
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP', 'error_code' => 500]);
        }
    }
    
    public function verifyOtp(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        $otpData = Otp::where('email', $email)->first();

        if (!$otpData) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        if ($otpData->otp !== $otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }
        return response()->json(['message' => 'OTP successfully matched.'], 200);
    }
    
    public function changePassword(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->password = Hash::make($password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
    
    public function imageUploadBase64(Request $request)
    {
        $imageData = $request->input('image');
        $imageData = str_replace('data:image/png;base64,', '', $imageData); 
        $imageData = str_replace(' ', '+', $imageData); 

        $decodedImage = base64_decode($imageData);

        $fileName = uniqid('image_') . '.png';

        Storage::disk('public')->put($fileName, $decodedImage);

        $filePath = Storage::disk('public')->url($fileName);
        $uploadedImageResponse = array(
            "image_name" => basename($fileName),
            "image_url" => $filePath,
        );
        return response()->json(['message' => 'File Uploaded Successfully', 'data' => $uploadedImageResponse], 200);
    }
    
    public function fileUpload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = uniqid('file_') . '.' . $file->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('', $file, $fileName);
            $filePath = Storage::disk('public')->url($fileName);
            $uploadedFileResponse = [
                "file_name" => $fileName,
                "file_url" => $filePath,
            ];

            return response()->json(['message' => 'File Uploaded Successfully', 'data' => $uploadedFileResponse], 200);
        } else {
            return response()->json(['message' => 'No file was provided'], 400);
        }
    }
    
    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $userId = $request->input('id');
        $userToUpdate = User::find($userId);

        if (!$userToUpdate) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userToUpdate->fill($request->only(['fname', 'lname', 'address', 'phone']));
        $userToUpdate->save();

        return response()->json(['message' => 'User updated successfully', 'data' => $userToUpdate], 200);
    }
    
    public function getUser()
    {
        $user = Auth::user();
        return response()->json(['data' => $user], 200);
    }
    
    public function getUsers()
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'admin') {
            $users = User::where('role', '<>', 'admin')->get();
            return response()->json(['data' => $users], 200);
        } else {
            return response()->json(['error' => 'You do not have permission to access this data.'], 403);
        }
    }
    
    public function getUserById($id)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'admin') {
            $requestedUser = User::find($id);

            if ($requestedUser) {
                return response()->json(['data' => $requestedUser], 200);
            } else {
                return response()->json(['error' => 'User not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'You do not have permission to access this data.'], 403);
        }
    }
    
    public function logout()
    {
        $access_token = auth()->user()->token();
        $tokenRepository = app(TokenRepository::class);
        $tokenRepository->revokeAccessToken($access_token->id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.'
        ], 200);
    }
    
    public function getUserRequests()
    {
        $user = Auth::user();
        if ($user->role == 'admin') {
            // Eager load the related user information using the 'user' relationship
            $requests = Requests::with('user')->get();

            // Return the requests along with the associated user information
            return response()->json(['data' => $requests], 200);
        } else {
            return response()->json(['error' => 'You do not have permission to access this data.'], 403);
        }
    }
    
     public function adminApproveRequests(Request $request)
    {
        $user = Auth::user();
    
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'You do not have permission to access this data.'], 403);
        }
    
        $userId = $request->input('user_id');
        $password = $request->input('password');
        $request_status = $request->input('request');
        $active_status = $request->input('active_status');
    
        $userToUpdate = User::find($userId);
        $requestToUpdate = Requests::where('user_id', $userId)->first();
    
        if (!$userToUpdate || !$requestToUpdate) {
            return response()->json(['error' => 'User or Request not found'], 404);
        }
    
        if ($password) {
            $passwordHashed = Hash::make($password);
            $userToUpdate->password = $passwordHashed;
        }
    
        $userToUpdate->fill($request->only(['active_status']));
        $requestToUpdate->fill($request->only(['active_status', 'request']));
    
        $requestToUpdate->save();
        $userToUpdate->save();
    
        if ($active_status == 1) {
            try {
                $email = $userToUpdate->email;
                $data = [
                    'password' => 'uber1234',
                    'email' => $email,
                    'message' => 'Your OTP Code For Verify Email'
                ];
    
                $view = view('passwordTemplate', compact('data', 'email', 'password'))->render();
                
                // Define the mail data
                $mailData = [
                    'subject' => 'OTP Code',
                    'to' => $email,
                    'view' => $view,
                    'password' => 'uber1234'
                ];
    
                $userToUpdate->password = $password;
                $userToUpdate->save();
    
                $this->sendPasswordMail($mailData);
                
                return response()->json(['message' => 'Credentials sent successfully']);
            } catch (\Exception $e) {
                 return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            }
        }
        
        return response()->json(['message' => 'User updated successfully', 'data' => $userToUpdate], 200);
    }

    private function sendPasswordMail($mailData){
        $vieww = $mailData['view'];
        $apiURL = 'https://api.sendinblue.com/v3/smtp/email';
        $postInput = [
            "subject" => $mailData['subject'],
            "sender" => [
                "name" => $mailData['subject'],
                "email" => "noahconner1512@gmail.com"
            ],
            "to" => [
                [
                    "name" => "Noah Conner",
                    "email" => $mailData['to'],
                ]
            ],
            "htmlContent" => $vieww
        ];
        $headers = [
            'accept' => 'application/json',
            'api-key' => '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',
            'content-type' => 'application/json'
        ];
    
        $response = Http::withHeaders($headers)->post($apiURL, $postInput);
        return $response;
    }

    
  
}

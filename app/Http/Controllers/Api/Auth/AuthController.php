<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\VonageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;



use Carbon\Carbon;

class AuthController extends BaseController
{


    public function __construct(VonageService $vonageService)
{
    $this->vonageService = $vonageService;
}

public function login(Request $request)
{


    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|max:11',
        'password' => 'required|string|min:8',
    ]);


    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::where('phone',$request->phone )->first();


    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }
    if ($user->phone_verified_at) {
        $token = JWTAuth::fromUser($user);
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    return response()->json(['message' => 'Phone number not verified. Please verify your phone number first.'], 403);
}

public function register(Request $request)
{

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone' => 'required|string|unique:users,phone|max:11',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Create the user
    $user = User::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'phone' => $request->phone,
        'password' => bcrypt($request->password),
    ]);


    $verificationCode = rand(100000, 999999);
    VerificationCode::create([
        'user_id' => $user->id,
        'code' => $verificationCode,
        'expires_at' => Carbon::now()->addMinutes(4),
    ]);


    $this->vonageService->sendOtp($user->phone, $verificationCode);

    return response()->json(['message' => 'User registered successfully. Verification code sent.']);
}


public function verifyPhone(Request $request)
{

    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|max:11',
        'code' => 'required|string|max:6',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }
    {



    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
}

    $verificationCode = VerificationCode::where('user_id', $user->id)
        ->where('code', $request->code)
        ->where('expires_at', '>', Carbon::now())
        ->first();

    if (!$verificationCode) {
        return response()->json(['message' => 'Invalid or expired verification code.'], 400);
    }


    $user->phone_verified_at = now();
    $user->save();

    $verificationCode->delete();

    return response()->json(['message' => 'Phone number verified successfully.']);
}
}

public function sendOtp(Request $request)
{

    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|max:11',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }


    if (!$user->phone_verified_at) {
        $verificationCode = rand(100000, 999999); // توليد رمز تحقق عشوائي
        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $verificationCode,
            'expires_at' => Carbon::now()->addMinutes(10), // تحديد مدة انتهاء الرمز
        ]);

        $this->vonageService->sendOtp($user->phone, $verificationCode);

        return response()->json(['message' => 'Verification code sent to your phone.']);
    } else {
        return response()->json(['message' => 'Phone number already verified.']);
    }
}



}

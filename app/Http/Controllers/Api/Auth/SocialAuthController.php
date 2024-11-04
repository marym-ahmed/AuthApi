<?php

namespace App\Http\Controllers\Api\Auth;
use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    public function socialRegister(Request $request)
{

    $provider = $request->input('provider');
    $accessToken = $request->input('access_token');
    if ($provider === 'google') {
        $user = Socialite::driver('google')->userFromToken($accessToken);
    } elseif ($provider === 'facebook') {
        $user = Socialite::driver('facebook')->userFromToken($accessToken);
    } else {
        return response()->json(['error' => 'Invalid provider'], 400);
    }

    $nameParts = explode(' ', $user->getName(), 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    $userInDB = User::updateOrCreate(
        ['email' => $user->getEmail()],
        [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'provider_id' => $user->getId(),
            'provider' => $provider,
        ]
    );
    $token = auth()->login($userInDB);
    return response()->json(['token' => $token]);
}


}

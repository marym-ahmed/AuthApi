<?php

namespace App\Services;

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class VonageService
{
    protected $client;

    public function __construct()
    {
        // استرجاع المفاتيح من ملف البيئة
        $apiKey = env('VONAGE_API_KEY');
        $apiSecret = env('VONAGE_API_SECRET');

        // إعداد العميل باستخدام المفاتيح
        $credentials = new Basic($apiKey, $apiSecret);
        $this->client = new Client($credentials);
    }

    // دالة لإرسال OTP
    public function sendOtp($phoneNumber, $verificationCode)
    {
        // Create an SMS message instance
        $message = new SMS($phoneNumber, 'Zebehty', "Your verification code is: $verificationCode");

        // Send the message
        $response = $this->client->sms()->send($message);

        return $response;
    }
}

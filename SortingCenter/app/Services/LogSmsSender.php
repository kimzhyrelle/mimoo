<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    public static ?string $lastPhone = null;

    public static ?string $lastMessage = null;

    public function send(string $phone, string $message): void
    {
        static::$lastPhone = $phone;
        static::$lastMessage = $message;

        Log::info('SMS OTP dispatched', ['phone' => $phone, 'message' => $message]);
    }
}

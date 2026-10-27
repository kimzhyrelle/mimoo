<?php

namespace App\Services;

interface SmsSender
{
    public function send(string $phone, string $message): void;
}

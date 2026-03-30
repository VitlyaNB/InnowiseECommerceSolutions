<?php

namespace App\Services\Interfaces;

interface AuthTokenServiceInterface
{
    public function createForUserId(int $userId): string;
}

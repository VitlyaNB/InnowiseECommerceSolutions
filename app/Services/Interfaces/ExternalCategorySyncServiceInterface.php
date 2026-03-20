<?php

namespace App\Services\Interfaces;

use App\Dto\ExternalCategorySyncResultDto;

interface ExternalCategorySyncServiceInterface
{
    public function sync(): ExternalCategorySyncResultDto;
}

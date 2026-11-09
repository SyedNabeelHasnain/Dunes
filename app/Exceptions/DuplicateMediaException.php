<?php

namespace App\Exceptions;

use App\Models\MediaAsset;
use Exception;

class DuplicateMediaException extends Exception
{
    protected MediaAsset $duplicateAsset;

    public function __construct(string $message, MediaAsset $duplicateAsset, int $code = 409)
    {
        parent::__construct($message, $code);
        $this->duplicateAsset = $duplicateAsset;
    }

    public function getDuplicateAsset(): MediaAsset
    {
        return $this->duplicateAsset;
    }
}

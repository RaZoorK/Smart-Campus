<?php

namespace App\Service;

class JsonDecoderService
{
    public function decode(string $jsonString): array
    {
        return json_decode($jsonString, true);
    }
}

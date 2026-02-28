<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class ImageCompressedDto extends Data
{
    public function __construct(
        public string $encoded,
        public string $fileType,
        public string $path,
        public string $hash,
    ) {
    }
}

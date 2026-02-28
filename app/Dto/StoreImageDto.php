<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class StoreImageDto extends Data
{
    public function __construct(
        public string $hash,
        public string $mime_type,
        public string $name,
        public string $path,
        public string $disk,
        public int    $original_size,
        public int    $size,
        public int    $reference_count = 1
    ) {
    }
}

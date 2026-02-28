<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    public function compress(UploadedFile $file): array
    {
        $image = Image::read($file);

        $image->orient();

        if ($image->width() > 2000) {
            $image->scale(width: 2000);
        }

        // удаляет метаданные (EXIF, ICC profile, GPS, camera info и т.д.)
        if (method_exists($image, 'strip')) {
            $image->strip();
        }

        $encoder = new WebpEncoder(quality: 85);
        $encoded = (string) $image->encode($encoder);
        $fileType = $image->encode($encoder)->mediaType();

        $path = 'images/' . Str::uuid() . '.webp';

        return [$encoded, $fileType, $path];
    }
}

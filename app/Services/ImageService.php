<?php

namespace App\Services;

use App\Dto\ImageCompressedDto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    public function compress(UploadedFile $file): ImageCompressedDto
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
        $encodedImage = $image->encode($encoder);
        $encoded = (string) $encodedImage;
        $fileType = $encodedImage->mediaType();

        $hash = hash('sha256', $encoded);

        $path = 'images/' . Str::uuid() . '.webp';

        return ImageCompressedDto::from([
            'encoded' => $encoded,
            'fileType' => $fileType,
            'path' => $path,
            'hash' => $hash,
        ]);
    }
}

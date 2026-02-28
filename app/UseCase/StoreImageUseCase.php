<?php

namespace App\UseCase;

use App\Dto\StoreImageDto;
use App\Models\Image;
use App\Repository\ImageRepository;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

readonly class StoreImageUseCase
{
    public function __construct(
        private ImageService    $imageService,
        private ImageRepository $imageRepository,
    ) {
    }

    public function execute(UploadedFile $file): Image
    {
        $compressedDto = $this->imageService->compress($file);
        $image = $this->imageRepository->getByHash($compressedDto->hash);

        if ($image) {
            $existing = $this->imageRepository->getById($image->id);
            if ($existing) {
                return $existing;
            }

            $image->increment('reference_count');
            auth()->user()->images()->attach($image->id);

            return $image;
        }

        Storage::disk('images')->put($compressedDto->path, $compressedDto->encoded);

        $storeImageDto = StoreImageDto::from([
            'name' => $file->getClientOriginalName(),
            'hash' => $compressedDto->hash,
            'path' => $compressedDto->path,
            'disk' => 'images',
            'mime_type' => $compressedDto->fileType,
            'original_size' => $file->getSize(),
            'size' => Storage::disk('images')->size($compressedDto->path),
        ]);

        $newImage = $this->imageRepository->upload($storeImageDto);
        auth()->user()->images()->attach($newImage->id);

        return $newImage;
    }
}

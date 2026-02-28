<?php

namespace App\Jobs;

use App\Enums\ImageStatus;
use App\Repository\ImageRepository;
use App\Services\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessImageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $imageId,
        private readonly int $userId,
    ) {
    }

    public function handle(ImageService $imageService, ImageRepository $imageRepository): void
    {
        $image = $imageRepository->findById($this->imageId);

        if (!$image || $image->status !== ImageStatus::PENDING) {
            return;
        }

        try {
            $fileContent = Storage::disk('temp')->get($image->path);
            $compressedDto = $imageService->compress($fileContent);

            Storage::disk('temp')->delete($image->path);

            $existing = $imageRepository->getByHash($compressedDto->hash);

            if ($existing) {
                if (!$imageRepository->isOwnedByUser($existing, $this->userId)) {
                    $imageRepository->incrementReferenceCount($existing);
                    $imageRepository->attachUser($existing, $this->userId);
                }

                $imageRepository->detachUser($image, $this->userId);
                $imageRepository->delete($image);

                return;
            }

            Storage::disk('images')->put($compressedDto->path, $compressedDto->encoded);

            $imageRepository->markAsReady(
                $image,
                $compressedDto,
                Storage::disk('images')->size($compressedDto->path),
            );

        } catch (Throwable $e) {
            Storage::disk('temp')->delete($image->path);
            $imageRepository->markAsFailed($image);

            throw $e;
        }
    }
}

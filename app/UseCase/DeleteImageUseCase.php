<?php

namespace App\UseCase;

use App\Repository\ImageRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class DeleteImageUseCase
{
    public function __construct(
        private ImageRepository $imageRepository,
    ) {
    }

    public function execute(int $imageId): void
    {
        $user = auth()->user();

        $image = $this->imageRepository->getById($imageId);
        if (!$image) {
            throw new NotFoundHttpException('Image not found.');
        }

        $image->users()->detach($user);

        $image->decrement('reference_count');

        if ($image->reference_count <= 0) {
            Storage::disk($image->disk)->delete($image->path);
            $this->imageRepository->delete($image);
        }
    }
}

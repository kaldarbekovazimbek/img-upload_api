<?php

namespace App\UseCase;

use App\Models\User;
use App\Repository\ImageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class DeleteImageUseCase
{
    public function __construct(
        private ImageRepository $imageRepository,
    ) {
    }

    public function execute(int $imageId, User $user): void
    {
        $fileToDelete = DB::transaction(function () use ($imageId, $user) {
            $image = $this->imageRepository->getById($imageId, $user->id);

            if (!$image) {
                throw new NotFoundHttpException('Image not found.');
            }

            $image->users()->detach($user->id);
            $image->decrement('reference_count');
            $image->refresh();

            if ($image->reference_count <= 0) {
                $disk = $image->disk;
                $path = $image->path;
                $this->imageRepository->delete($image);

                return ['disk' => $disk, 'path' => $path];
            }

            return null;
        });

        if ($fileToDelete) {
            Storage::disk($fileToDelete['disk'])->delete($fileToDelete['path']);
        }
    }
}

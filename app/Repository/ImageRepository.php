<?php

namespace App\Repository;

use App\Dto\ImageCompressedDto;
use App\Enums\ImageStatus;
use App\Models\Image;
use Illuminate\Pagination\AbstractPaginator;

class ImageRepository
{
    public function getById(int $id, int $userId): ?Image
    {
        return Image::query()
            ->where('id', $id)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->first();
    }

    public function getByUser(int $userId): AbstractPaginator
    {
        return Image::query()
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->latest()
            ->simplePaginate(15);
    }

    public function findById(int $id): ?Image
    {
        return Image::query()->where('id', $id)->first();
    }

    public function getByHash(string $hash): ?Image
    {
        return Image::query()->where('hash', $hash)->first();
    }

    public function markAsReady(Image $image, ImageCompressedDto $dto, int $size): void
    {
        $image->hash = $dto->hash;
        $image->mime_type = $dto->fileType;
        $image->path = $dto->path;
        $image->disk = 'images';
        $image->size = $size;
        $image->status = ImageStatus::READY;
        $this->save($image);
    }

    public function markAsFailed(Image $image): void
    {
        $image->status = ImageStatus::FAILED;
        $this->save($image);
    }

    public function attachUser(Image $image, int $userId): void
    {
        $image->users()->attach($userId);
    }

    public function detachUser(Image $image, int $userId): void
    {
        $image->users()->detach($userId);
    }

    public function incrementReferenceCount(Image $image): void
    {
        $image->increment('reference_count');
    }

    public function isOwnedByUser(Image $image, int $userId): bool
    {
        return $image->users()->where('users.id', $userId)->exists();
    }

    public function save(Image $image): void
    {
        $image->save();
    }

    public function delete(Image $image): void
    {
        $image->delete();
    }
}

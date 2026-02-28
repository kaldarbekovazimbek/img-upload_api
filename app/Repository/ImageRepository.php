<?php

namespace App\Repository;

use App\Dto\StoreImageDto;
use App\Models\Image;

class ImageRepository
{
    public function getById($id): ?Image
    {
        $userId = auth()->id();

        return Image::query()
            ->where('id', $id)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->first();
    }

    public function upload(StoreImageDto $dto): Image
    {
        $image = new Image();
        $image->hash = $dto->hash;
        $image->mime_type = $dto->mime_type;
        $image->name = $dto->name;
        $image->disk = $dto->disk;
        $image->path = $dto->path;
        $image->original_size = $dto->original_size;
        $image->size = $dto->size;
        $image->reference_count = $dto->reference_count;
        $this->save($image);
        return $image;
    }
    public function save(Image $image): void
    {
        $image->save();
    }
    public function delete(Image $image): void
    {
        $image->delete();
    }

    public function getByHash(string $hash): ?Image
    {
        return Image::query()->where('hash', $hash)->first();
    }
}

<?php

namespace App\UseCase;

use App\Enums\ImageStatus;
use App\Jobs\ProcessImageJob;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreImageUseCase
{
    public function execute(UploadedFile $file, User $user): Image
    {
        $tempPath = 'pending/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk('temp')->put($tempPath, $file->get());

        $image = DB::transaction(function () use ($file, $user, $tempPath) {
            $image = new Image();
            $image->name = $file->getClientOriginalName();
            $image->original_size = $file->getSize();
            $image->size = $file->getSize();
            $image->mime_type = $file->getMimeType();
            $image->path = $tempPath;
            $image->disk = 'temp';
            $image->reference_count = 1;
            $image->status = ImageStatus::PENDING;
            $image->save();

            $user->images()->attach($image->id);

            return $image;
        });

        ProcessImageJob::dispatch($image->id, $user->id);

        return $image;
    }
}

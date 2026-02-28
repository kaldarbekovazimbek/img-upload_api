<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {
    }

    public function index()
    {
        $images = auth()->user()->images()->latest()->get();

        return response()->json($images);
    }

    public function upload(UploadImageRequest $request)
    {
        $file = $request->file('image');

        [$binaryData, $fileType, $path] = $this->imageService->compress($file);

        // hash от нормализованных байтов
        $hash = hash('sha256', $binaryData);

        // ищем существующий файл
        $existing = Image::query()->where('hash', $hash)->first();

        if ($existing) {
            if ($request->user()->images()->where('image_id', $existing->id)->exists()) {
                return response()->json($existing, 200);
            }

            $existing->increment('reference_count');
            $request->user()->images()->attach($existing->id);

            return response()->json($existing, 201);
        }

        // если новый файл — сохраняем на диск
        Storage::disk('images')->put($path, $binaryData);

        // создаём запись в базе
        $newImage = Image::create([
            "name" => $file->getClientOriginalName(),
            'hash' => $hash,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $fileType,
            'original_size' => $file->getSize(),
            'size' => Storage::disk('public')->size($path),
            'reference_count' => 1,
        ]);

        $request->user()->images()->attach($newImage->id);

        return response()->json($newImage, 201);
    }

    public function get(Request $request, int $id)
    {
        $image = auth()->user()->images()->find($id);

        if (!$image) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        return response()->json($image);
    }

    public function delete(Request $request, int $id)
    {
        $user = auth()->user();

        if (!$user->images()->where('image_id', $id)->exists()) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        $image = Image::find($id);

        $user->images()->detach($id);

        $image->decrement('reference_count');

        if ($image->reference_count <= 0) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        return response()->json(['message' => 'Image deleted.']);
    }
}

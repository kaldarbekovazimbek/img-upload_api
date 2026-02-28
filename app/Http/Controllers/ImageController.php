<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index()
    {
        $images = auth()->user()->images()->latest()->get();

        return response()->json($images);
    }

    public function upload(UploadImageRequest $request)
    {
        $disk = 'images';
        $file = $request->file('image');

        $hash = hash_file('sha256', $file->getRealPath());

        $existing = Image::where('hash', $hash)->first();

        if ($existing) {
            if ($request->user()->images()->where('image_id', $existing->id)->exists()) {
                return response()->json($existing, 200);
            }

            $existing->increment('reference_count');
            $request->user()->images()->attach($existing->id);

            return response()->json($existing, 201);
        }

        $path = $file->store('', $disk);

        $image = Image::create([
            'name'            => $file->getClientOriginalName(),
            'path'            => $path,
            'disk'            => $disk,
            'mime_type'       => $file->getClientMimeType(),
            'hash'            => $hash,
            'original_size'   => $file->getSize(),
            'size'            => Storage::disk($disk)->size($path),
            'reference_count' => 1,
        ]);

        $request->user()->images()->attach($image->id);

        return response()->json($image, 201);
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

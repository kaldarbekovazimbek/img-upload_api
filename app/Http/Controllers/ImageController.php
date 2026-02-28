<?php

namespace App\Http\Controllers;

use App\Enums\ApiCode;
use App\Http\Requests\UploadImageRequest;
use App\Http\Responses\ApiResponse;
use App\Repository\ImageRepository;
use App\UseCase\DeleteImageUseCase;
use App\UseCase\StoreImageUseCase;

class ImageController extends Controller
{
    public function index()
    {
        $images = auth()->user()->images()->latest()->simplePaginate(15);

        return ApiResponse::success($images);
    }

    public function upload(UploadImageRequest $request, StoreImageUseCase $storeImageUseCase)
    {
        $image = $storeImageUseCase->execute($request->file('image'));

        return ApiResponse::success($image, 'Image uploaded successfully.', 201);
    }

    public function show(int $id, ImageRepository $imageRepository)
    {
        $image = $imageRepository->getById($id);

        if (!$image) {
            return ApiResponse::error(ApiCode::IMAGE_NOT_FOUND, 'Image not found.', 404);
        }

        return ApiResponse::success($image);
    }

    public function delete(int $id, DeleteImageUseCase $deleteImageUseCase)
    {
        $deleteImageUseCase->execute($id);

        return ApiResponse::success(null, 'Image deleted successfully.');
    }
}

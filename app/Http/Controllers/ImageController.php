<?php

namespace App\Http\Controllers;

use App\Enums\ApiCode;
use App\Http\Requests\UploadImageRequest;
use App\Http\Responses\ApiResponse;
use App\Repository\ImageRepository;
use App\UseCase\DeleteImageUseCase;
use App\UseCase\StoreImageUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
    ) {
    }
    public function index(Request $request): JsonResponse
    {
        $images = $this->imageRepository->getByUser($request->user()->id);

        return ApiResponse::success($images);
    }

    public function upload(UploadImageRequest $request, StoreImageUseCase $storeImageUseCase): JsonResponse
    {
        $image = $storeImageUseCase->execute($request->file('image'), $request->user());

        return ApiResponse::success($image, 'Image accepted, processing in background.', 202);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $image = $this->imageRepository->getById($id, $request->user()->id);

        if (!$image) {
            return ApiResponse::error(ApiCode::IMAGE_NOT_FOUND, 'Image not found.', 404);
        }

        return ApiResponse::success($image);
    }

    public function delete(int $id, Request $request, DeleteImageUseCase $deleteImageUseCase): JsonResponse
    {
        $deleteImageUseCase->execute($id, $request->user());

        return ApiResponse::success(null, 'Image deleted successfully.');
    }
}

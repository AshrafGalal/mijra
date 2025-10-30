<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UploadFileRequest;
use App\Services\UploadFileService;
use Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;

class UploadFileController extends Controller
{
    public function __invoke(UploadFileRequest $request, UploadFileService $uploadFileService)
    {
        try {
            $fileData = $uploadFileService->handleChunkUpload(request: $request);

            return ApiResponse::success(data: $fileData);
        } catch (UploadMissingFileException|UploadFailedException|\Exception $exception) {
            return ApiResponse::serverError(message: $exception->getMessage());
        }
    }
}

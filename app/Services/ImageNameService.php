<?php

namespace App\Services;

class ImageNameService
{
    public function __construct(
        private IFileService $fileService
    ) {
    }

    public function replaceImageName(?string $previousImageName, ?string $requestedImageName): ?string
    {
        if ($previousImageName === null) {
            return $requestedImageName;
        }

        if ($requestedImageName === null || $requestedImageName === '') {
            return $previousImageName;
        }

        $extension = pathinfo($previousImageName, PATHINFO_EXTENSION);
        $newImageName = $requestedImageName;

        if (!pathinfo($newImageName, PATHINFO_EXTENSION) && $extension !== '') {
            $newImageName .= '.' . $extension;
        }

        if ($previousImageName !== $newImageName) {
            $this->fileService->rename($previousImageName, $newImageName);
        }

        return $newImageName;
    }
}
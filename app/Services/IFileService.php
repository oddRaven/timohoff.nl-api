<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

interface IFileService
{
    /**
     * Upload a file to the remote storage.
     *
     * @param UploadedFile $file
     * @return array{success: bool, filename: string, path?: string, message?: string}
     */
    public function upload(UploadedFile $file): array;

    /**
     * Rename a file on the remote storage.
     *
     * @param string $oldPath
     * @param string $newPath
     * @return array{success: bool, message?: string}
     */
    public function rename(string $oldPath, string $newPath): array;

    /**
     * Delete a file from the remote storage.
     *
     * @param string $path
     * @return array{success: bool, message?: string}
     */
    public function delete(string $path): array;
}

<?php

namespace App\Services;

use Throwable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FtpsFileService implements IFileService
{
    private const CONNECTIVITY_HINTS = [
        'timed out' => 'Connection timed out. Verify host/port reachability and firewall rules.',
        'connection refused' => 'Connection refused. The FTPS service may be down or the wrong port is used.',
        'no route to host' => 'No route to host. Check network routing or outbound restrictions.',
        'name or service not known' => 'Hostname could not be resolved. Verify FTPS_HOST DNS/IP.',
        'unable to authenticate' => 'Authentication failed. Verify username and password.',
        'permission denied' => 'Permission denied by remote server. Verify credentials and target path rights.',
        'ssl' => 'SSL/TLS handshake failed. Verify FTPS_SSL setting and server TLS support.',
    ];

    private const DISK = 'ftps';

    /**
     * Upload a file to FTPS storage.
     *
     * @param UploadedFile $file
     * @return array{success: bool, filename: string, path?: string, message?: string, error?: string, root_cause?: string, hints?: array}
     */
    public function upload(UploadedFile $file, $filename = null): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'filename' => $file->getClientOriginalName(),
                'message' => 'Invalid upload',
                'error' => $file->getErrorMessage(),
            ];
        }

        try {
            if ($filename === null) {
                $filename = $file->getClientOriginalName();
            }
            $path = $file->storeAs('', $filename, self::DISK);

            if ($path === false) {
                return [
                    'success' => false,
                    'filename' => $filename,
                    'message' => 'File could not be stored',
                ];
            }

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path,
                'message' => 'File uploaded successfully',
            ];
        } catch (Throwable $e) {
            $errorChain = $this->exceptionChain($e);
            $root = $errorChain[count($errorChain) - 1] ?? null;
            $rootMessage = $root['message'] ?? $e->getMessage();
            $hints = $this->connectionHints($errorChain);

            Log::error('FTPS upload failed', [
                'context' => 'FtpsFileService@upload',
                'disk' => self::DISK,
                'host' => config('filesystems.disks.ftps.host'),
                'port' => config('filesystems.disks.ftps.port'),
                'root' => config('filesystems.disks.ftps.root'),
                'exception_chain' => $errorChain,
            ]);

            $response = [
                'success' => false,
                'filename' => $file->getClientOriginalName(),
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
                'root_cause' => $rootMessage,
                'hints' => $hints,
            ];

            if (config('app.debug')) {
                $response['exception_chain'] = $errorChain;
                $response['ftps'] = [
                    'disk' => self::DISK,
                    'host' => config('filesystems.disks.ftps.host'),
                    'port' => config('filesystems.disks.ftps.port'),
                    'root' => config('filesystems.disks.ftps.root'),
                    'private_key_path' => config('filesystems.disks.ftps.privateKey'),
                    'use_agent' => config('filesystems.disks.ftps.useAgent'),
                ];
            }

            return $response;
        }
    }

    /**
     * Rename a file on FTPS storage.
     *
     * @param string $oldPath
     * @param string $newPath
     * @return array{success: bool, message?: string, error?: string}
     */
    public function rename(string $oldPath, string $newPath): array
    {
        try {
            if (!Storage::disk(self::DISK)->exists($oldPath)) {
                return [
                    'success' => false,
                    'message' => 'File not found',
                ];
            }

            Storage::disk(self::DISK)->move($oldPath, $newPath);

            return [
                'success' => true,
                'message' => 'File renamed successfully',
            ];
        } catch (Throwable $e) {
            Log::error('FTPS rename failed', [
                'context' => 'FtpsFileService@rename',
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'File rename failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a file from FTPS storage.
     *
     * @param string $path
     * @return array{success: bool, message?: string, error?: string}
     */
    public function delete(string $path): array
    {
        try {
            if (!Storage::disk(self::DISK)->exists($path)) {
                return [
                    'success' => false,
                    'message' => 'File not found',
                ];
            }

            Storage::disk(self::DISK)->delete($path);

            return [
                'success' => true,
                'message' => 'File deleted successfully',
            ];
        } catch (Throwable $e) {
            Log::error('FTPS delete failed', [
                'context' => 'FtpsFileService@delete',
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'File deletion failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the exception chain for error reporting.
     *
     * @param Throwable $e
     * @return array<array{type: string, message: string, code: int|string}>
     */
    private function exceptionChain(Throwable $e): array
    {
        $chain = [];
        $current = $e;

        while ($current !== null) {
            $chain[] = [
                'type' => $current::class,
                'message' => $current->getMessage(),
                'code' => $current->getCode(),
            ];

            $current = $current->getPrevious();
        }

        return $chain;
    }

    /**
     * Extract connectivity hints from exception chain.
     *
     * @param array<array{type: string, message: string, code: int|string}> $errorChain
     * @return array<string>
     */
    private function connectionHints(array $errorChain): array
    {
        $messages = strtolower(implode(' | ', array_map(
            fn (array $item) => (string)($item['message'] ?? ''),
            $errorChain
        )));

        $hints = [];

        foreach (self::CONNECTIVITY_HINTS as $needle => $hint) {
            if (str_contains($messages, $needle)) {
                $hints[] = $hint;
            }
        }

        return $hints;
    }
}

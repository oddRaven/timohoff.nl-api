<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
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

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        try {
            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid upload',
                    'error' => $file?->getErrorMessage() ?? 'No file uploaded or upload corrupted',
                ], 422);
            }

            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('', $filename, 'ftps');

            if ($path === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'File could not be stored',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'filename' => $filename,
                'path' => $path,
            ], 201);
        } catch (Throwable $e) {
            $errorChain = $this->exceptionChain($e);
            $root = $errorChain[count($errorChain) - 1] ?? null;
            $rootMessage = $root['message'] ?? $e->getMessage();
            $hints = $this->connectionHints($errorChain);

            Log::error('FTPS upload failed', [
                'context' => 'FileController@upload',
                'disk' => 'ftps',
                'host' => config('filesystems.disks.ftps.host'),
                'port' => config('filesystems.disks.ftps.port'),
                'root' => config('filesystems.disks.ftps.root'),
                'exception_chain' => $errorChain,
            ]);

            $response = [
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
                'root_cause' => $rootMessage,
                'hints' => $hints,
            ];

            if (config('app.debug')) {
                $response['exception_chain'] = $errorChain;
                $response['ftps'] = [
                    'disk' => 'ftps',
                    'host' => config('filesystems.disks.ftps.host'),
                    'port' => config('filesystems.disks.ftps.port'),
                    'root' => config('filesystems.disks.ftps.root'),
                    'private_key_path' => config('filesystems.disks.ftps.privateKey'),
                    'use_agent' => config('filesystems.disks.ftps.useAgent'),
                ];
            }

            return response()->json([
                ...$response,
            ], 500);
        }
    }

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

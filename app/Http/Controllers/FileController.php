<?php

namespace App\Http\Controllers;

use App\Services\IFileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private IFileService $fileService
    ) {
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid upload',
                'error' => 'No file uploaded',
            ], 422);
        }

        $filename = $request->name;
        if (!pathinfo((string) $filename, PATHINFO_EXTENSION)) {
            $filename .= '.' . $file->getClientOriginalExtension();
        }

        $result = $this->fileService->upload($file, $filename);

        $statusCode = $result['success'] ? 201 : 422;

        return response()->json($result, $statusCode);
    }
}

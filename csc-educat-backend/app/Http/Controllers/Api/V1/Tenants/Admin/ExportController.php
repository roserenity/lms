<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }
    public function index()
    {
        $exports = Exports::when(request('type'), function($query){
                $query->whereIn('type', request('type'));
            })
            ->where('user_id', $this->authUser()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(request('paginate'));

        return $this->sendResponse($exports, 'List of all exports', 200);
    }

    public function destroy($id)
    {
        $export = Exports::findOrFail($id);

        try {
            if (Storage::disk('s3')->exists($export->path)) {
                Storage::disk('s3')->delete($export->path);
            }
            
            $export->delete();
            return $this->sendResponse([], 'Exported file successfully deleted', 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete export file: ' . $e->getMessage());
    
            return $this->sendError('Failed to delete exported file.', 500);
        }

    }

    public function downloadExportFile(Request $request)
    {

        $request->validate([
            'url' => 'required|url',
        ]);

        $path = ltrim(parse_url($request->url, PHP_URL_PATH), '/');
        $parts = explode('/', $path);
        $file_path = implode('/', array_slice($parts, 1));

        $expires = 60;
        // Create temporary URL
        $temporary_url = Storage::disk('s3')->temporaryUrl(
            $file_path,
            now()->addMinutes($expires),
            [
                'ResponseContentType' => 'application/octet-stream',
                'ResponseContentDisposition' => 'attachment; filename="' . basename($file_path) . '"',
            ]
        );

        return response()->json([
            'result' => 'success',
            'downloadable_url' => $temporary_url,
        ]);

    }


}

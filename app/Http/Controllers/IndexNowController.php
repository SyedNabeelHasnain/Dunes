<?php

namespace App\Http\Controllers;

use App\Services\IndexNowService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexNowController extends Controller
{
    /**
     * Serve the IndexNow verification key text file.
     * Required by Bing, Yandex, and other search engines to verify domain ownership.
     */
    public function verifyKey(Request $request, string $key, IndexNowService $indexNow): Response
    {
        $configuredKey = $indexNow->getKey();

        if ($key !== $configuredKey) {
            abort(404, 'IndexNow key not found.');
        }

        return response($configuredKey, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

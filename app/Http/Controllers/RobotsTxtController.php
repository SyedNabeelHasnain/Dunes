<?php

namespace App\Http\Controllers;

use App\Services\Seo\RobotsTxtService;
use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    public function show(RobotsTxtService $robots): Response
    {
        return response($robots->getCurrentContent(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

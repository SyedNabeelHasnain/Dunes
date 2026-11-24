<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Seo\KeywordIntelligenceService;
use Illuminate\View\View;

class AdminKeywordController extends Controller
{
    public function index(KeywordIntelligenceService $keywords): View
    {
        $report = $keywords->buildReport();

        return view('admin.settings.keywords', $report);
    }
}

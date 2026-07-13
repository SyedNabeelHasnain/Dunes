<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the Terms and Conditions page.
     */
    public function terms()
    {
        $page = LegalPage::where('slug', 'terms-condition')
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();

        return view('legal.show', compact('page'));
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        $page = LegalPage::where('slug', 'privacy-policy')
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();

        return view('legal.show', compact('page'));
    }
}

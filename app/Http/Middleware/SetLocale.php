<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't run locale prefix logic on admin, api, or ajax routes
        if ($request->is('admin*') || $request->is('api*') || $request->is('ajax*')) {
            App::setLocale('en');
            Carbon::setLocale('en');
            view()->share('currentLocale', 'en');
            view()->share('isRtl', false);
            view()->share('textDir', 'ltr');
            return $next($request);
        }

        $segment1 = $request->segment(1);

        // Rule 1: English is root default. Any request to /en or /en/* gets 301-redirected to / or /*
        if ($segment1 === 'en') {
            $segments = $request->segments();
            array_shift($segments);
            $targetPath = '/' . implode('/', $segments);
            $queryString = $request->getQueryString();
            $targetUrl = $targetPath . ($queryString ? '?' . $queryString : '');

            return redirect($targetUrl, 301);
        }

        // Fetch active languages
        try {
            $activeLanguages = Language::getActive();
        } catch (\Throwable $e) {
            $activeLanguages = Language::fallbackCollection();
        }

        // Rule 2: Check if segment 1 matches an active non-default language
        $matchedLang = $activeLanguages->firstWhere('code', $segment1);

        if ($matchedLang && ! $matchedLang->is_default) {
            $currentLocale = $matchedLang->code;
            $currentLanguage = $matchedLang;
        } else {
            $currentLanguage = $activeLanguages->firstWhere('is_default', true) ?: $activeLanguages->first();
            $currentLocale = $currentLanguage ? $currentLanguage->code : 'en';
        }

        // Set application & Carbon locale
        App::setLocale($currentLocale);
        Carbon::setLocale($currentLocale);

        if ($currentLocale !== 'en') {
            URL::defaults(['locale' => $currentLocale]);
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $currentLocale);
        }

        $isRtl = $currentLanguage ? $currentLanguage->isRtl() : false;
        $textDir = $isRtl ? 'rtl' : 'ltr';

        // Share globally with Blade views
        view()->share('currentLocale', $currentLocale);
        view()->share('currentLanguage', $currentLanguage);
        view()->share('isRtl', $isRtl);
        view()->share('textDir', $textDir);
        view()->share('activeLanguages', $activeLanguages);

        return $next($request);
    }
}

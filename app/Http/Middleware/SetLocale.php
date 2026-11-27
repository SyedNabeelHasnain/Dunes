<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Services\Http\InternalHttpProbeService;
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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't run locale prefix logic on admin, api, or ajax routes
        if ($request->is('admin*') || $request->is('api*') || $request->is('ajax*')) {
            App::setLocale('en');
            Carbon::setLocale('en');
            $this->clearLocaleUrlDefault();
            view()->share('currentLocale', 'en');
            view()->share('isRtl', false);
            view()->share('textDir', 'ltr');

            return $next($request);
        }

        $segment1 = $request->segment(1);

        // Rule 1: English is root default. Any request to /en or /en/* gets 301-redirected to / or /* (safe against /\evil.com open redirect)
        if ($segment1 === 'en') {
            $segments = $request->segments();
            array_shift($segments);
            $safeRest = ltrim(str_replace('\\', '', implode('/', $segments)), '/');
            $targetPath = '/' . $safeRest;
            $queryString = $request->getQueryString();
            $targetUrl = $targetPath.($queryString ? '?'.$queryString : '');

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
            // If this request matched the {locale} route group with an inactive/unsupported 2-letter code,
            // never render a duplicate English page at /{unknown_code}/...
            if ($request->route('locale') !== null || ($segment1 && preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $segment1))) {
                $segments = $request->segments();
                $knownInactiveLocales = ['fr', 'de', 'zh', 'pt', 'nl', 'tr', 'pl', 'ja', 'ko', 'hi', 'ur', 'fa', 'he', 'id', 'ms', 'th', 'vi', 'sv', 'no', 'da', 'fi', 'el', 'cs', 'ro', 'hu', 'uk'];
                if (count($segments) > 1 || in_array($segment1, $knownInactiveLocales, true)) {
                    array_shift($segments);
                    $safeRest = ltrim(str_replace('\\', '', implode('/', $segments)), '/');
                    $targetPath = $safeRest === '' ? '/' : '/'.$safeRest;
                    $queryString = $request->getQueryString();

                    return redirect($targetPath.($queryString ? '?'.$queryString : ''), 301);
                }

                abort(404);
            }

            $currentLanguage = $activeLanguages->firstWhere('is_default', true) ?: $activeLanguages->first();
            $currentLocale = $currentLanguage ? $currentLanguage->code : 'en';
        }

        // Set application & Carbon locale
        App::setLocale($currentLocale);
        Carbon::setLocale($currentLocale);

        if ($currentLocale !== 'en') {
            URL::defaults(['locale' => $currentLocale]);
        } else {
            $this->clearLocaleUrlDefault();
        }

        if (! InternalHttpProbeService::isProbing() && $request->hasSession()) {
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

    private function clearLocaleUrlDefault(): void
    {
        $defaults = URL::getDefaultParameters();
        if (! array_key_exists('locale', $defaults)) {
            return;
        }

        unset($defaults['locale']);
        $urlGenerator = app('url');
        $reset = \Closure::bind(function (array $params): void {
            if (is_object($this->routeGenerator)) {
                $this->routeGenerator->defaultParameters = $params;
            }
        }, $urlGenerator, \Illuminate\Routing\UrlGenerator::class);
        $reset($defaults);
    }
}

<?php

use App\Models\Language;

if (! function_exists('buildHtmlAttrs')) {
    function buildHtmlAttrs(array $attrs = []): string
    {
        $parts = [];
        foreach ($attrs as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $k = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            if ($value === true) {
                $parts[] = $k;
            } else {
                $parts[] = $k.'="'.htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8').'"';
            }
        }

        return $parts ? ' '.implode(' ', $parts) : '';
    }
}

if (! function_exists('renderFloatingInput')) {
    function renderFloatingInput(array $opts = []): string
    {
        $type = $opts['type'] ?? 'text';
        $id = $opts['id'] ?? '';
        $name = $opts['name'] ?? '';
        $label = $opts['label'] ?? '';
        $placeholder = $opts['placeholder'] ?? '';
        $autocomplete = $opts['autocomplete'] ?? '';
        $required = ! empty($opts['required']);
        $wrapperClass = $opts['wrapperClass'] ?? 'relative rounded-xl';
        $inputClass = $opts['inputClass'] ?? 'w-full rounded-xl border border-slate-200 px-3.5 pt-5 pb-2 text-base sm:text-xs font-semibold text-slate-800 outline-hidden focus:border-primary';
        $labelClass = $opts['labelClass'] ?? 'absolute text-[10px] uppercase font-bold text-slate-400 start-3.5 top-1.5 transition-all pointer-events-none';
        $wrapperAttrs = $opts['wrapperAttrs'] ?? [];
        $inputAttrs = $opts['inputAttrs'] ?? [];
        $labelAttrs = $opts['labelAttrs'] ?? [];

        $wrapperAttrs['class'] = $wrapperClass;
        $inputAttrs['type'] = $type;
        $inputAttrs['id'] = $id;
        $inputAttrs['name'] = $name;
        $inputAttrs['class'] = $inputClass;

        if ($placeholder !== '') {
            $inputAttrs['placeholder'] = $placeholder;
        }
        if ($autocomplete !== '') {
            $inputAttrs['autocomplete'] = $autocomplete;
        }
        if ($required) {
            $inputAttrs['required'] = true;
        }
        if ($labelClass !== '') {
            $labelAttrs['class'] = $labelClass;
        }
        $labelAttrs['for'] = $id;

        return '<div'.buildHtmlAttrs($wrapperAttrs).'><input'.buildHtmlAttrs($inputAttrs).'><label'.buildHtmlAttrs($labelAttrs).'>'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</label></div>';
    }
}

if (! function_exists('renderFloatingTextarea')) {
    function renderFloatingTextarea(array $opts = []): string
    {
        $id = $opts['id'] ?? '';
        $name = $opts['name'] ?? '';
        $label = $opts['label'] ?? '';
        $placeholder = $opts['placeholder'] ?? '';
        $autocomplete = $opts['autocomplete'] ?? '';
        $required = ! empty($opts['required']);
        $wrapperClass = $opts['wrapperClass'] ?? 'relative rounded-xl';
        $inputClass = $opts['inputClass'] ?? 'w-full rounded-xl border border-slate-200 px-3.5 pt-5 pb-2 text-xs font-semibold text-slate-800 outline-hidden focus:border-primary';
        $labelClass = $opts['labelClass'] ?? 'absolute text-[10px] uppercase font-bold text-slate-400 left-3.5 top-1.5 transition-all pointer-events-none';
        $wrapperAttrs = $opts['wrapperAttrs'] ?? [];
        $inputAttrs = $opts['inputAttrs'] ?? [];
        $labelAttrs = $opts['labelAttrs'] ?? [];

        $wrapperAttrs['class'] = $wrapperClass;
        $inputAttrs['id'] = $id;
        $inputAttrs['name'] = $name;
        $inputAttrs['class'] = $inputClass;

        if ($placeholder !== '') {
            $inputAttrs['placeholder'] = $placeholder;
        }
        if ($autocomplete !== '') {
            $inputAttrs['autocomplete'] = $autocomplete;
        }
        if ($required) {
            $inputAttrs['required'] = true;
        }
        if ($labelClass !== '') {
            $labelAttrs['class'] = $labelClass;
        }
        $labelAttrs['for'] = $id;

        return '<div'.buildHtmlAttrs($wrapperAttrs).'><textarea'.buildHtmlAttrs($inputAttrs).'></textarea><label'.buildHtmlAttrs($labelAttrs).'>'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</label></div>';
    }
}

if (! function_exists('formatPhone')) {
    function formatPhone(string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', $phone);
    }
}

if (! function_exists('formatWhatsApp')) {
    function formatWhatsApp(string $number, string $message = ''): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        return "https://wa.me/{$number}".($message ? '?text='.urlencode($message) : '');
    }
}

if (! function_exists('localized_route')) {
    /**
     * Generate a localized URL for a named route.
     */
    function localized_route(string $name, mixed $parameters = [], ?string $locale = null, bool $absolute = true): string
    {
        $targetLocale = $locale ?: app()->getLocale();

        // English is the default root locale - no prefix
        if ($targetLocale === 'en' || empty($targetLocale)) {
            $baseName = str_starts_with($name, 'locale.') ? substr($name, 7) : $name;
            if ($baseName === 'home') {
                return $absolute ? (rtrim(url('/'), '/').'/') : '/';
            }

            return route($baseName, $parameters, $absolute);
        }

        // Non-default locale: if named localized route exists, use it directly
        $localizedName = str_starts_with($name, 'locale.') ? $name : "locale.{$name}";
        $routes = app('routes');
        if ($routes && $routes->hasNamedRoute($localizedName)) {
            if (is_array($parameters)) {
                $params = array_merge(['locale' => $targetLocale], $parameters);
            } elseif ($parameters !== null && $parameters !== '') {
                $routeObj = $routes->getByName($localizedName);
                $paramNames = $routeObj ? $routeObj->parameterNames() : [];
                $secondParam = $paramNames[1] ?? 'slug';
                $params = ['locale' => $targetLocale, $secondParam => $parameters];
            } else {
                $params = ['locale' => $targetLocale];
            }

            return route($localizedName, $params, $absolute);
        }

        // Fallback: manually construct localized URL
        $baseName = str_starts_with($name, 'locale.') ? substr($name, 7) : $name;
        $relativePath = route($baseName, $parameters, false);
        $cleanPath = preg_replace('#^/[a-z]{2}(-[a-z]{2})?(?=/|$)#', '', $relativePath);
        $localizedPath = ($cleanPath === '/' || $cleanPath === '') ? "/{$targetLocale}" : "/{$targetLocale}{$cleanPath}";

        return $absolute ? url($localizedPath) : $localizedPath;
    }
}

if (! function_exists('switch_locale_url')) {
    /**
     * Generate the clean canonical URL to switch the current page to the target locale.
     */
    function switch_locale_url(string $targetLocale): string
    {
        $request = request();
        $segments = $request->segments();

        // Check if the first segment is an active non-default language
        if (! empty($segments)) {
            $first = $segments[0];
            $knownCodes = ['en', 'ar', 'ru', 'es', 'it'];
            try {
                $languages = Language::getActive();
                if (($languages->contains('code', $first) || in_array($first, $knownCodes, true)) && $first !== 'en') {
                    array_shift($segments);
                } elseif ($first === 'en') {
                    array_shift($segments);
                }
            } catch (Throwable $e) {
                if (in_array($first, $knownCodes, true)) {
                    array_shift($segments);
                }
            }
        }

        if ($targetLocale === 'en' || empty($targetLocale)) {
            $path = empty($segments) ? '/' : '/'.implode('/', $segments);
        } else {
            $path = empty($segments) ? "/{$targetLocale}" : "/{$targetLocale}/".implode('/', $segments);
        }

        $baseUrl = ($path === '/') ? rtrim(url('/'), '/').'/' : url($path);

        $pageNum = (int) $request->query('page', 0);
        if ($pageNum > 1 && empty($request->except('page'))) {
            return $baseUrl.'?page='.$pageNum;
        }

        return $baseUrl;
    }
}

if (! function_exists('resolve_og_image')) {
    /**
     * Normalize any OG image path or URL to an absolute existing asset URL.
     */
    function resolve_og_image(?string $raw, string $fallback = 'images/desert-safari-poster.avif'): string
    {
        if (empty($raw)) {
            return asset($fallback);
        }

        $legacyMap = [
            'og-default.jpg' => 'images/desert-safari-poster.avif',
            'og-home.jpg' => 'images/evening-desert-safari-dubai-dune-discovery-tourism.avif',
            'og-tours.jpg' => 'images/dubai-desert-safari-tour-dune-discovery-tourism.avif',
            'og-blog.jpg' => 'images/desert-safari-dubai-dune-discovery-tourism-HD.avif',
            'og-about.jpg' => 'images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
            'og-contact.jpg' => 'images/dubai-desert-safari-camp-dune-discovery-tourism.avif',
            'og-faq.jpg' => 'images/dubai-desert-safari-dune-bashing-dune-discovery-tourism.avif',
            'og-rate-card.jpg' => 'images/dubai-desert-safari-bbq-dune-discovery-tourism.avif',
        ];

        $basename = basename(parse_url($raw, PHP_URL_PATH) ?: $raw);
        if (isset($legacyMap[$basename])) {
            return asset($legacyMap[$basename]);
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        $clean = ltrim($raw, '/');
        if (! str_starts_with($clean, 'images/') && ! str_starts_with($clean, 'storage/') && ! str_starts_with($clean, 'uploads/')) {
            $clean = 'images/'.$clean;
        }

        return asset($clean);
    }
}

if (! function_exists('resolve_tour_image')) {
    /**
     * Resolve a tour image path (seeded filename, storage upload, or absolute URL) to a valid web URL.
     */
    function resolve_tour_image(?string $raw, string $fallback = 'images/desert-safari-poster.avif'): string
    {
        if (empty($raw)) {
            return asset($fallback);
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        $clean = ltrim($raw, '/');

        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        if (str_starts_with($clean, 'tours/') || str_starts_with($clean, 'uploads/')) {
            return asset('storage/'.$clean);
        }

        if (str_starts_with($clean, 'images/')) {
            return asset($clean);
        }

        if (file_exists(public_path('images/'.$clean))) {
            return asset('images/'.$clean);
        }

        if (file_exists(storage_path('app/public/'.$clean))) {
            return asset('storage/'.$clean);
        }

        $base = preg_replace('/\.(jpe?g|png|webp|avif)$/i', '', $clean);
        foreach (['.avif', '.webp', '.jpg', '.jpeg', '.png'] as $ext) {
            if (file_exists(public_path('images/'.$base.$ext))) {
                return asset('images/'.$base.$ext);
            }
            if (file_exists(storage_path('app/public/'.$base.$ext))) {
                return asset('storage/'.$base.$ext);
            }
        }

        return asset('images/'.$clean);
    }
}

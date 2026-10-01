<?php

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
        $inputClass = $opts['inputClass'] ?? 'w-full rounded-xl border border-slate-200 px-3.5 pt-5 pb-2 text-xs font-semibold text-slate-800 outline-hidden focus:border-primary';
        $labelClass = $opts['labelClass'] ?? 'absolute text-[10px] uppercase font-bold text-slate-400 left-3.5 top-1.5 transition-all pointer-events-none';
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
        $locale = $locale ?: app()->getLocale();

        // English is the default root locale - no prefix
        if ($locale === 'en' || empty($locale)) {
            return route($name, $parameters, $absolute);
        }

        // For non-default locales (e.g. ar), prefix the path with /{locale}
        $relativePath = route($name, $parameters, false);
        $localizedPath = ($relativePath === '/' || $relativePath === '') ? "/{$locale}" : "/{$locale}{$relativePath}";

        return $absolute ? url($localizedPath) : $localizedPath;
    }
}

if (! function_exists('switch_locale_url')) {
    /**
     * Generate the URL to switch the current page to the target locale.
     */
    function switch_locale_url(string $targetLocale): string
    {
        $request = request();
        $segments = $request->segments();

        // Check if the first segment is an active non-default language
        if (! empty($segments)) {
            $first = $segments[0];
            try {
                $languages = \App\Models\Language::getActive();
                if ($languages->contains('code', $first) && $first !== 'en') {
                    array_shift($segments);
                } elseif ($first === 'en') {
                    array_shift($segments);
                }
            } catch (\Throwable $e) {
                if ($first === 'en' || $first === 'ar') {
                    array_shift($segments);
                }
            }
        }

        if ($targetLocale === 'en' || empty($targetLocale)) {
            $path = empty($segments) ? '/' : '/'.implode('/', $segments);
        } else {
            $path = empty($segments) ? "/{$targetLocale}" : "/{$targetLocale}/".implode('/', $segments);
        }

        $queryString = $request->getQueryString();
        $fullUrl = url($path).($queryString ? '?'.$queryString : '');

        return $fullUrl;
    }
}


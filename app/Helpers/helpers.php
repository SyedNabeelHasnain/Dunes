<?php

if (!function_exists('buildHtmlAttrs')) {
    function buildHtmlAttrs(array $attrs = []): string
    {
        $parts = [];
        foreach ($attrs as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $k = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            if ($value === true) {
                $parts[] = $k;
            } else {
                $parts[] = $k . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        return $parts ? ' ' . implode(' ', $parts) : '';
    }
}

if (!function_exists('renderFloatingInput')) {
    function renderFloatingInput(array $opts = []): string
    {
        $type = $opts['type'] ?? 'text';
        $id = $opts['id'] ?? '';
        $name = $opts['name'] ?? '';
        $label = $opts['label'] ?? '';
        $placeholder = $opts['placeholder'] ?? '';
        $autocomplete = $opts['autocomplete'] ?? '';
        $required = !empty($opts['required']);
        $wrapperClass = $opts['wrapperClass'] ?? 'form-floating';
        $inputClass = $opts['inputClass'] ?? 'form-control';
        $labelClass = $opts['labelClass'] ?? '';
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

        return '<div' . buildHtmlAttrs($wrapperAttrs) . '><input' . buildHtmlAttrs($inputAttrs) . '><label' . buildHtmlAttrs($labelAttrs) . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label></div>';
    }
}

if (!function_exists('renderFloatingTextarea')) {
    function renderFloatingTextarea(array $opts = []): string
    {
        $id = $opts['id'] ?? '';
        $name = $opts['name'] ?? '';
        $label = $opts['label'] ?? '';
        $placeholder = $opts['placeholder'] ?? '';
        $autocomplete = $opts['autocomplete'] ?? '';
        $required = !empty($opts['required']);
        $wrapperClass = $opts['wrapperClass'] ?? 'form-floating';
        $inputClass = $opts['inputClass'] ?? 'form-control';
        $labelClass = $opts['labelClass'] ?? '';
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

        return '<div' . buildHtmlAttrs($wrapperAttrs) . '><textarea' . buildHtmlAttrs($inputAttrs) . '></textarea><label' . buildHtmlAttrs($labelAttrs) . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label></div>';
    }
}

if (!function_exists('formatPhone')) {
    function formatPhone(string $phone): string
    {
        return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
    }
}

if (!function_exists('formatWhatsApp')) {
    function formatWhatsApp(string $number, string $message = ''): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        return "https://wa.me/{$number}" . ($message ? '?text=' . urlencode($message) : '');
    }
}

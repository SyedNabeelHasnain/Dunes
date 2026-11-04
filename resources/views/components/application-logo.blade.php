@php
    $appLogo = !empty($settings['site_logo'])
        ? (str_starts_with($settings['site_logo'], 'http') ? $settings['site_logo'] : asset(ltrim($settings['site_logo'], '/')))
        : asset('images/logo.png');
@endphp
<img src="{{ $appLogo }}" alt="{{ config('app.name', 'Dunes Discovery Tourism') }}" {{ $attributes->merge(['class' => 'h-12 w-auto object-contain']) }}>

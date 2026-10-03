<?php

namespace App\Services\Routing;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;

class LocalizedUrlGenerator extends UrlGenerator
{
    /**
     * Get the URL to a named route, automatically prefixing with current active locale
     * if not default and if a localized counterpart exists.
     *
     * @param  string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $locale = App::getLocale();

        if ($locale && $locale !== 'en' && ! str_starts_with($name, 'locale.') && ! str_starts_with($name, 'admin.') && ! str_starts_with($name, 'api.')) {
            $localizedName = "locale.{$name}";
            if ($this->routes->hasNamedRoute($localizedName)) {
                $params = is_array($parameters)
                    ? array_merge(['locale' => $locale], $parameters)
                    : ['locale' => $locale, 'slug' => $parameters];

                return parent::route($localizedName, $params, $absolute);
            }
        }

        return parent::route($name, $parameters, $absolute);
    }
}

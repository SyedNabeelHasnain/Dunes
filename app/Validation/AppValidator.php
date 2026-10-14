<?php

namespace App\Validation;

use Illuminate\Validation\Validator;

class AppValidator extends Validator
{
    /**
     * Validate that an attribute is an active image (including AVIF, WebP, SVG when allowed, JPG, PNG, GIF, BMP).
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @return bool
     */
    public function validateImage($attribute, $value, $parameters = [])
    {
        $mimes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'avif'];

        if (is_array($parameters) && in_array('allow_svg', $parameters)) {
            $mimes[] = 'svg';
        }

        return $this->validateMimes($attribute, $value, $mimes);
    }
}

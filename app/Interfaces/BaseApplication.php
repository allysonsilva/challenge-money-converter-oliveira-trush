<?php

namespace App;

use Illuminate\Foundation\Application as LaravelApplication;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class BaseApplication extends LaravelApplication
{
    protected $namespace = 'App\\';

    public function path($path = '') // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app/Interfaces' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

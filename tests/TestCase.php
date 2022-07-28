<?php

namespace Tests;

use Tests\Traits\CustomMacros;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use WithFaker;
    use CustomMacros;
    use CreatesApplication;

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[CustomMacros::class])) {
            $this->testResponseMacros();
        }

        return $uses;
    }

    protected function laravelEncrypterToCookieAuth(): void
    {
        // For `app('encrypter')->decrypt` code inside `getCookie` method!
        $this->app->instance('encrypter', app('cookie-auth-encrypter'));
    }

    protected function withSameDomain()
    {
        return $this->withHeader('referer', '127.0.0.1')->withCredentials();
    }

    protected function logged()
    {
        $this->laravelEncrypterToCookieAuth();

        return $this->withSameDomain()->withCookie('api_token', json_encode(['user' => ['id' => 1]]));
    }
}

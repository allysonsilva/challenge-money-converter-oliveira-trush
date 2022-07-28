<?php

namespace Core\Providers;

use Core\Auth\CookieAuthGuard;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Auth;
use Core\Auth\Exceptions\MissingCookieAppKeyException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array
     *
     * @phpstan-var array<class-string<\Illuminate\Database\Eloquent\Model>, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAuthCookieEncrypter();
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->configureDriveAuthCookie();
    }

    /**
     * Configure the new drive cookie authentication guard.
     *
     * @return void
     */
    protected function configureDriveAuthCookie(): void
    {
        Auth::resolved(function ($auth) {
            // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
            $auth->extend(config('auth.cookies.guard.driver'), function ($app, $name, array $config) use ($auth) {
                $encrypter = app('cookie-auth-encrypter');

                $guard = new CookieAuthGuard(
                    $encrypter,
                    $auth->createUserProvider($config['provider']),
                );

                if (method_exists($guard, 'setDispatcher')) {
                    $guard->setDispatcher($this->app['events']);
                }

                if (method_exists($guard, 'setCookieJar')) {
                    $guard->setCookieJar($this->app['cookie']);
                }

                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));

                return $guard;
            });
        });
    }

    /**
     * Register the encrypter.
     *
     * @return void
     */
    protected function registerAuthCookieEncrypter(): void
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        $this->app->bind('cookie-auth-encrypter', function ($app) {
            if (empty($key = app('config')->get('auth.cookies.key'))) {
                throw new MissingCookieAppKeyException();
            }

            return new Encrypter($key, 'AES-256-CBC');
        });
    }
}

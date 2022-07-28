<?php

namespace App\API\Auth\Http\Controllers;

use App\API\Auth\Http\Requests\LoginRequest;
use Support\Http\Controller as SupportController;
use App\API\Auth\Http\Controllers\Traits\CookieAuth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LoginController extends SupportController
{
    use CookieAuth;

    /**
     * Create a new Controller instance.
     */
    public function __construct()
    {
    }

    /**
     * Handle a login request to the application.
     *
     * @param \App\API\Auth\Http\Requests\LoginRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function __invoke(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        // attempt to verify the credentials and create a token for the user
        // verify the credentials and create a token for the user
        if (empty($userData = $this->login($credentials))) {
            return response()->json(['error' => __('auth.failed')], HttpResponse::HTTP_UNAUTHORIZED);
        }

        return response()->json(data: $userData);
    }
}

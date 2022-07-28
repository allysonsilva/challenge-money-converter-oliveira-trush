<?php

namespace App\API\Auth\Http\Controllers;

use Support\Http\Controller as SupportController;
use App\API\Auth\Http\Controllers\Traits\CookieAuth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LogoutController extends SupportController
{
    use CookieAuth;

    /**
     * Log the user out (remove browser cookie).
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function __invoke()
    {
        $this->logoutCookie();

        return response()->json(status: HttpResponse::HTTP_NO_CONTENT);
    }
}

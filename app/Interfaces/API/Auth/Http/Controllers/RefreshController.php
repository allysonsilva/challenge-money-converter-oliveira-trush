<?php

namespace App\API\Auth\Http\Controllers;

use Illuminate\Support\Carbon;
use Support\Http\Controller as SupportController;
use App\API\Auth\Http\Controllers\Traits\CookieAuth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RefreshController extends SupportController
{
    use CookieAuth;

    /**
     * Refresh a cookie.
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function __invoke()
    {
        [$apiTokenCookie, $apiRefreshCookie] = $this->refreshCookie();

        $apiTokenCarbon = Carbon::createFromTimestamp($apiTokenCookie->getExpiresTime());
        $apiRefreshCarbon = Carbon::createFromTimestamp($apiRefreshCookie->getExpiresTime());

        $data = [
            ...$this->respondWithUserData(),
            ...[
                'session' => [
                    'access' => $apiTokenCarbon->toIso8601String(),
                    'refresh' => $apiRefreshCarbon->toIso8601String(),
                ],
            ],
        ];

        return response()->json(data: $data, status: HttpResponse::HTTP_OK);
    }
}

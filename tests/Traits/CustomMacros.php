<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait CustomMacros
{
    /**
     * Cria macros personalizadas de response.
     *
     * @return void
     */
    protected function testResponseMacros(): void
    {
        TestResponse::macro('assertResource', function (JsonResource $resource): void {
            $responseData = $resource->response()->getData(true);

            $this->assertJson($responseData);
        });
    }
}

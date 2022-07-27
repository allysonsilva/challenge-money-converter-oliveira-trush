<?php

declare(strict_types=1);

namespace Support\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * @codeCoverageIgnore
 */
abstract class BaseDto extends DataTransferObject implements Arrayable, Responsable
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)// phpcs:ignore
    {
        return response()->json(['data' => $this->toArray()]);
    }
}

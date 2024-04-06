<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestHandler
{
    public static function getRequestParameter(Request $request, string $parameter, bool $required = false): mixed
    {
        if ($request->getMethod() === Request::METHOD_GET) {
            if (!$request->query->has($parameter) && $required) {
                throw new BadRequestHttpException("$parameter is required");
            }
            return $request->query->get($parameter);
        } else {
            $body = json_decode($request->getContent(), true) ?? [];
            if (!key_exists($parameter, $body) && $required) {
                throw new BadRequestHttpException("$parameter is required");
            }
            return key_exists($parameter, $body) ? $body[$parameter] : null;
        }
    }

    public static function getTwoRequestParameters(Request $request, string $first, string $second): array {
        $firstValue = static::getRequestParameter($request, $first);
        $secondValue = static::getRequestParameter($request, $second);
        if (!$firstValue && !$secondValue) {
            throw new BadRequestHttpException("at least $first or $second are required");
        }
        return [$firstValue, $secondValue];
    }
}

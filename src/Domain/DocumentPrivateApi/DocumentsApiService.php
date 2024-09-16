<?php

namespace App\Domain\DocumentPrivateApi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class DocumentsApiService
{
    public function __construct(
        private readonly string $docsApiAccessKey
    )
    {
    }

    public function assertAuthentication(Request $request): void
    {
        if (!$request->headers->has("Authorization")) throw new UnauthorizedHttpException("Bearer");
        $accessKey = str_replace("Bearer ", "", $request->headers->get("Authorization"));
        if ($accessKey !== $this->docsApiAccessKey) throw new UnauthorizedHttpException("Bearer");
    }
}

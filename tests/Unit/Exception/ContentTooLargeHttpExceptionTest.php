<?php

namespace App\Tests\Unit\Exception;

use App\Exception\ContentTooLargeHttpException;
use PHPUnit\Framework\TestCase;

class ContentTooLargeHttpExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new ContentTooLargeHttpException('Content too large');

        $this->assertEquals(413, $exception->getStatusCode());
        $this->assertEquals('Content too large', $exception->getMessage());
    }
}

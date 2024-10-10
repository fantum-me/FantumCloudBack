<?php

namespace App\Tests\Unit\Utils;

use App\Utils\RequestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestHandlerTest extends TestCase
{
    public function testGetTwoRequestParametersBothPresent()
    {
        $request = new Request(
            ['first_param' => 'value1', 'second_param' => 'value2'], // GET parameters
            [],
            [],
            [],
            [],
            ['REQUEST_METHOD' => 'GET']
        );

        $result = RequestHandler::getTwoRequestParameters($request, 'first_param', 'second_param');
        $this->assertEquals(['value1', 'value2'], $result);
    }

    public function testGetTwoRequestParametersMissingBoth()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('at least first_param or second_param are required');

        RequestHandler::getTwoRequestParameters($request, 'first_param', 'second_param');
    }

    public function testGetRequestParameterGetMethod()
    {
        $request = new Request(
            ['test_param' => 'value'], // GET parameters
            [],
            [],
            [],
            [],
            ['REQUEST_METHOD' => 'GET']
        );

        $result = RequestHandler::getRequestParameter($request, 'test_param');
        $this->assertEquals('value', $result);
    }

    public function testGetRequestParameterPostMethod()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST'], json_encode(['test_param' => 'value']));

        $result = RequestHandler::getRequestParameter($request, 'test_param');
        $this->assertEquals('value', $result);
    }

    public function testGetRequestParameterRequiredMissingGetMethod()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('test_param is required');

        RequestHandler::getRequestParameter($request, 'test_param', true);
    }

    public function testGetRequestParameterRequiredMissingPostMethod()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST'], json_encode([]));
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('test_param is required');

        RequestHandler::getRequestParameter($request, 'test_param', true);
    }

    public function testGetRequestParameterMissingOptionalPostMethod()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST'], json_encode([]));

        $result = RequestHandler::getRequestParameter($request, 'test_param');
        $this->assertNull($result);
    }

    public function testGetRequestParameterMissingOptionalGetMethod()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);

        $result = RequestHandler::getRequestParameter($request, 'test_param');
        $this->assertNull($result);
    }
}

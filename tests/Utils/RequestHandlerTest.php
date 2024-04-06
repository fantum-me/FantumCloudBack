<?php

namespace App\Tests\Utils;

use App\Utils\RequestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestHandlerTest extends TestCase
{
    public function testGetValidParameterFromGetRequest(): void
    {
        $parameter = "parameter";
        $value = "value";
        $request = new Request([$parameter => $value]);
        $request->setMethod(Request::METHOD_GET);
        $this->assertEquals($value, RequestHandler::getRequestParameter($request, $parameter));
    }

    public function testGetInvalidParameterFromGetRequest(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $this->assertNull(RequestHandler::getRequestParameter($request, "invalidParameter"));
    }

    public function testGetInvalidRequiredParameterFromGetRequest(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $this->expectException(BadRequestHttpException::class);
        RequestHandler::getRequestParameter($request, "invalidParameter", true);
    }

    public function testGetValidParameterFromPostRequest(): void
    {
        $parameter = "parameter";
        $value = "value";
        $request = new Request([], [], [], [], [], [], json_encode([$parameter => $value]));
        $request->setMethod(Request::METHOD_POST);
        $this->assertEquals($value, RequestHandler::getRequestParameter($request, $parameter));
    }

    public function testGetInvalidParameterFromPostRequest(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->assertNull(RequestHandler::getRequestParameter($request, "invalidParameter"));
    }

    public function testGetInvalidRequiredParameterFromPostRequest(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->expectException(BadRequestHttpException::class);
        RequestHandler::getRequestParameter($request, "invalidParameter", true);
    }

    public function testGetTwoValidRequestParameters(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->query->set("parameter1", "value1");
        $request->query->set("parameter2", "value2");
        [$value1, $value2] = RequestHandler::getTwoRequestParameters($request, "parameter1", "parameter2");
        $this->assertEquals("value1", $value1);
        $this->assertEquals("value2", $value2);
    }

    public function testGetOneOutOfTwoValidRequestParameters(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->query->set("parameter1", "value1");
        [$value1, $value2] = RequestHandler::getTwoRequestParameters($request, "parameter1", "parameter2");
        $this->assertEquals("value1", $value1);
        $this->assertNull($value2);
    }

    public function testGetTwoInvalidRequestParameters(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $this->expectException(BadRequestHttpException::class);
        RequestHandler::getTwoRequestParameters($request, "invalid", "anotherInvalid");
    }
}

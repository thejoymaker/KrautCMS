<?php

declare(strict_types=1);

use Kraut\Util\RequestBodyParserUtil;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;


class RequestBodyParserUtilTest extends TestCase
{
    public function testParseFormData()
    {
        $request = new ServerRequest('POST', '/test', ['Content-Type' => 'application/x-www-form-urlencoded']);
        $stream = Stream::create('key1=value1&key2=value2');
        $request = $request->withBody($stream);

        $parsedRequest = RequestBodyParserUtil::parseRequestBody($request, $this->createMock(LoggerInterface::class));

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $parsedRequest->getParsedBody());
    }

    public function testParseJsonData()
    {
        $request = new ServerRequest('POST', '/test', ['Content-Type' => 'application/json']);
        $stream = Stream::create('{"key1": "value1", "key2": "value2"}');
        $request = $request->withBody($stream);

        $parsedRequest = RequestBodyParserUtil::parseRequestBody($request, $this->createMock(LoggerInterface::class));

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $parsedRequest->getParsedBody());
    }

    public function testParseMultipartData()
    {
        $_POST = ['key1' => 'value1', 'key2' => 'value2'];
        $request = new ServerRequest('POST', '/test', ['Content-Type' => 'multipart/form-data']);

        $parsedRequest = RequestBodyParserUtil::parseRequestBody($request, $this->createMock(LoggerInterface::class));

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $parsedRequest->getParsedBody());
    }

    public function testInvalidJsonData()
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = new ServerRequest('POST', '/test', ['Content-Type' => 'application/json']);
        $stream = Stream::create('{"key1": "value1", "key2": "value2"'); // Invalid JSON
        $request = $request->withBody($stream);

        RequestBodyParserUtil::parseRequestBody($request, $this->createMock(LoggerInterface::class));
    }

    public function testGetBaseContentType()
    {
        $this->assertEquals('application/json', RequestBodyParserUtil::getBaseContentType('application/json; charset=utf-8'));
        $this->assertEquals('application/x-www-form-urlencoded', RequestBodyParserUtil::getBaseContentType('application/x-www-form-urlencoded'));
        $this->assertEquals('application/x-www-form-urlencoded', RequestBodyParserUtil::getBaseContentType(''));
    }
}
?>
<?php

declare(strict_types=1);

namespace Kraut\Middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;


class MalIntentDetectionMiddlewareTest extends TestCase
{
    private LoggerInterface $logger;
    private MalIntentDetectionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->middleware = new MalIntentDetectionMiddleware($this->logger);
    }

    public function testProcessWithMaliciousRequest(): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['param' => 'select * from users']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->logger->expects($this->once())->method('warning');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', (string) $response->getBody());
    }

    public function testProcessWithNonMaliciousRequest(): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['param' => 'safe input']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new Response());

        $this->logger->expects($this->never())->method('warning');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDetectMaliciousIntentWithMaliciousQueryParams(): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['param' => 'select * from users']);

        $this->assertTrue($this->middleware->detectMaliciousIntent($request));
    }

    public function testDetectMaliciousIntentWithNonMaliciousQueryParams(): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['param' => 'safe input']);

        $this->assertFalse($this->middleware->detectMaliciousIntent($request));
    }

    public function testDetectMaliciousIntentWithMaliciousBody(): void
    {
        $request = new ServerRequest('POST', '/test');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['param' => '<script>alert(1)</script>']));

        $this->assertTrue($this->middleware->detectMaliciousIntent($request));
    }

    public function testDetectMaliciousIntentWithNonMaliciousBody(): void
    {
        $request = new ServerRequest('POST', '/test');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['param' => 'safe input']));

        $this->assertFalse($this->middleware->detectMaliciousIntent($request));
    }
}

?>
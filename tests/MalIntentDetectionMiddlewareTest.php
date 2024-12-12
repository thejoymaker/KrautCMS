<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Server\RequestHandlerInterface;
use Kraut\Middleware\MalIntentDetectionMiddleware;

class MalIntentDetectionMiddlewareTest extends TestCase
{
    private $logger;
    private $middleware;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->middleware = new MalIntentDetectionMiddleware($this->logger);
    }

    public function testProcessWithNonMaliciousRequest(): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['param' => 'safe input']);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(200, [], 'OK');
            }
        };

        $this->logger->expects($this->never())->method('warning');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithMaliciousQueryParams(): void
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

    public function testProcessWithMaliciousBody(): void
    {
        $request = new ServerRequest('POST', '/test');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['param' => '<script>alert(1)</script>']));
        $request->getBody()->rewind();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->logger->expects($this->once())->method('warning');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', (string) $response->getBody());
    }

    public function testProcessWithNonMaliciousBody(): void
    {
        $request = new ServerRequest('POST', '/test');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['param' => 'hello world']));
        $request->getBody()->rewind();

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(200, [], 'OK');
            }
        };

        $this->logger->expects($this->never())->method('warning');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', (string) $response->getBody());
    }
}

?>
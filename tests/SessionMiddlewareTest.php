<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Session\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\Test\Mock\SessionMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * SessionMiddlewareTest tests
 */
class SessionMiddlewareTest extends TestCase
{
    protected function createSession(string $name = 'sess'): SessionInterface
    {
        return new Session(
            name: $name,
            maxlifetime: 1800,
            cookiePath: '/',
            cookieDomain: '',
            cookieSamesite: 'Strict',
            secure: true,
            httpOnly: true,
            saveHandler: null,
        );
    }
    
    public function testNameMethod()
    {
        // create middleware dispatcher.
        $dispatcher = new MiddlewareDispatcher(
            new FallbackHandler((new Psr17Factory())->createResponse(404)),
            new AutowiringMiddlewareFactory(new Container()) // any PSR-11 container
        );

        $session = new Session(
            name: 'sess',
            maxlifetime: 1800,
            cookiePath: '/',
            cookieDomain: '',
            cookieSamesite: 'Strict',
            secure: true,
            httpOnly: true,
            saveHandler: null,
        );

        $dispatcher->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

            $session = $request->getAttribute(SessionInterface::class);

            $hasSession = 'First[false]';
            
            if ($session instanceof SessionInterface) {
                $hasSession = 'First[true]';
            }
            
            $response = $handler->handle($request);
            $response->getBody()->write($hasSession);
            
            return $response;
        });

        $dispatcher->add(new SessionMiddleware($session));

        $dispatcher->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

            $session = $request->getAttribute(SessionInterface::class);

            $hasSession = 'Second[false]';
            
            if ($session instanceof SessionInterface) {
                $hasSession = 'Second[true]';
            }
            
            $response = $handler->handle($request);
            $response->getBody()->write($hasSession);
            
            return $response;
        });

        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.com');

        $response = $dispatcher->handle($request);
        
        $this->assertSame(
            'Second[true]First[false]',
            (string)$response->getBody()
        );        
    }
}
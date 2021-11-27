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
use Tobento\Service\Session\HttpUserAgentValidation;
use Tobento\Service\Session\ValidationInterface;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SessionValidationException;

/**
 * HttpUserAgentValidationTest tests
 */
class HttpUserAgentValidationTest extends TestCase
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
        );
    }

    public function testThatImplementsValidationInterface()
    {
        $this->assertInstanceOf(
            ValidationInterface::class,
            new HttpUserAgentValidation('Mozilla/5.0')
        );
    }
    
    public function testProcess()
    {
        $validation = new HttpUserAgentValidation('Mozilla/5.0');
        
        $session = $this->createSession()->deleteAll();
        
        $validation->process($session);
        
        $this->assertTrue(true);
    }
    
    public function testProcessThrowsSessionValidationExceptionIfRemoteAddrIsNotSame()
    {
        $this->expectException(SessionValidationException::class);
        
        $validation = new HttpUserAgentValidation(
            'Mozilla/5.0',
            '_session_httpUserAgent',
        );
        
        $session = $this->createSession()->deleteAll();
        $session->set('_session_httpUserAgent', 'Mozilla/4.0');
        
        $validation->process($session);
    }

    public function testHttpUserAgentMethod()
    {
        $validation = new HttpUserAgentValidation('Mozilla/5.0');
        
        $this->assertSame(
            'Mozilla/5.0',
            $validation->httpUserAgent()
        );
    }
}
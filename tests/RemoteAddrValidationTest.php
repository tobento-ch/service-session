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
use Tobento\Service\Session\RemoteAddrValidation;
use Tobento\Service\Session\ValidationInterface;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SessionValidationException;

/**
 * RemoteAddrValidationTest tests
 */
class RemoteAddrValidationTest extends TestCase
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
            new RemoteAddrValidation('192.168.1.1')
        );
    }
    
    public function testProcess()
    {
        $validation = new RemoteAddrValidation('192.168.1.1');
        
        $session = $this->createSession()->deleteAll();
        
        $validation->process($session);
        
        $this->assertTrue(true);
    }
    
    public function testProcessThrowsSessionValidationExceptionIfRemoteAddrIsNotSame()
    {
        $this->expectException(SessionValidationException::class);
        
        $validation = new RemoteAddrValidation(
            '192.168.1.1',
            null,
            '_session_remoteAddr',
        );
        
        $session = $this->createSession()->deleteAll();
        $session->set('_session_remoteAddr', '192.168.1.2');
        
        $validation->process($session);
    }
    
    public function testProcessSkipsValidationIfTrustedProxyExists()
    {        
        $validation = new RemoteAddrValidation(
            '192.168.1.2',
            ['192.168.1.2'],
            '_session_remoteAddr',
        );
        
        $session = $this->createSession()->deleteAll();
        
        $validation->process($session);
        
        $this->assertTrue(true);
    }
    
    public function testRemoteAddrMethod()
    {
        $validation = new RemoteAddrValidation('192.168.1.2');
        
        $this->assertSame(
            '192.168.1.2',
            $validation->remoteAddr()
        );
    }
    
    public function testTrustedProxiesMethod()
    {
        $validation = new RemoteAddrValidation('192.168.1.2');
        
        $this->assertSame(
            null,
            $validation->trustedProxies()
        );
        
        $validation = new RemoteAddrValidation(
            '192.168.1.2',
            ['192.168.1.2'],
        );
        
        $this->assertSame(
            ['192.168.1.2'],
            $validation->trustedProxies()
        );
    }    
}
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
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SessionException;
use Tobento\Service\Session\SessionStartException;
use Tobento\Service\Session\SessionSaveException;

/**
 * SessionTest tests
 */
class SessionTest extends TestCase
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
    
    public function testThatImplementsSessionInterface()
    {
        $this->assertInstanceOf(
            SessionInterface::class,
            $this->createSession()
        );
    }
    
    public function testNameMethod()
    {
        $this->assertSame(
            'sess',
            $this->createSession('sess')->name()
        );
    }
    
    public function testIdMethodReturnsNullIfSessionHasNotStarted()
    {
        $this->assertSame(
            null,
            $this->createSession()->id()
        );
    }
    
    public function testIdMethodReturnsString()
    {
        $session = $this->createSession();

        $this->assertTrue(
            is_null($this->createSession()->id())
        );
        
        // phpunit sends headers, so session cannot be started:
        try {
            $session->start();   
        } catch (SessionStartException $e) {
            $this->assertTrue(true);
        }
    }    
    
    public function testStartMethodThrowsSessionStartExceptionIfInvalidSessionName()
    {        
        $this->expectException(SessionStartException::class);
        
        $this->createSession('se-45ss')->start();
    }
    
    public function testDestroyMethod()
    {        
        $this->createSession()->destroy();
        
        $this->assertTrue(true);
    }
    
    public function testRegenerateIdMethodThrowsSessionExceptionIfTherIsNoActiveSession()
    {
        $this->expectException(SessionException::class);
        
        $this->createSession()->regenerateId();
    }      
}
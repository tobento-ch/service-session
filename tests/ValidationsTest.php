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
use Tobento\Service\Session\Validations;
use Tobento\Service\Session\HttpUserAgentValidation;
use Tobento\Service\Session\RemoteAddrValidation;
use Tobento\Service\Session\ValidationInterface;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SessionValidationException;

/**
 * ValidationsTest tests
 */
class ValidationsTest extends TestCase
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
            new Validations()
        );
    }
    
    public function testProcessWithoutAnyValidationIsSuccess()
    {
        $validation = new Validations();
        
        $session = $this->createSession()->deleteAll();
            
        $validation->process($session);
        
        $this->assertTrue(true);
    }
    
    public function testProcess()
    {
        $validation = new Validations(
            new RemoteAddrValidation('192.168.1.1'),
            new HttpUserAgentValidation('Mozilla/5.0'),
        );
        
        $session = $this->createSession()->deleteAll();
        
        $validation->process($session);
        
        $this->assertTrue(true);
    }    
    
    public function testProcessThrowsSessionValidationExceptionIfOneFails()
    {
        $this->expectException(SessionValidationException::class);

        $validation = new Validations(
            new RemoteAddrValidation('192.168.1.1'),
            new HttpUserAgentValidation('Mozilla/5.0', '_session_httpUserAgent'),
        );
                
        $session = $this->createSession()->deleteAll();
        $session->set('_session_httpUserAgent', 'Mozilla/4.0');
        
        $validation->process($session);
    }
    
    public function testValidationsMethod()
    {
        $validation = new Validations(
            new RemoteAddrValidation('192.168.1.1'),
            new HttpUserAgentValidation('Mozilla/5.0'),
        );
        
        $this->assertSame(
            2,
            count($validation->validations())
        );
    }    
}
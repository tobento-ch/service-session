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
use Tobento\Service\Session\SessionStartException;
use Tobento\Service\Session\SessionSaveException;

/**
 * SessionInteractingFlashTest tests
 */
class SessionInteractingFlashTest extends TestCase
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
    
    public function testFlashMethod()
    {
        $session = $this->createSession();
        
        $this->assertSame(
            $session,
            $session->flash('key', 'value')
        );
        
        // current request
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertSame('value', $session->get('key'));
        
        try {
            $session->save();
        } catch (SessionSaveException $e) {
            //
        }
        
        // next request        
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertTrue($session->has('key'));
        
        try {
            $session->save();
        } catch (SessionSaveException $e) {
            //
        }
        
        // after next request        
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertFalse($session->has('key'));
        
        try {
            $session->save();
        } catch (SessionSaveException $e) {
            //
        }        
    }
    
    public function testNowMethod()
    {
        $session = $this->createSession();
        
        $this->assertSame(
            $session,
            $session->now('key', 'value')
        );
        
        // current request
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertSame('value', $session->get('key'));
        
        try {
            $session->save();
        } catch (SessionSaveException $e) {
            //
        }
        
        // next request        
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertFalse($session->has('key'));    
    }
    
    public function testOnceMethod()
    {
        $session = $this->createSession();
        
        $this->assertSame(
            $session,
            $session->once('key', 'value')
        );
        
        // current request
        try {
            $session->start();
            $session->save();
        } catch (SessionStartException | SessionSaveException $e) {
            //
        }
        
        // next request        
        try {
            $session->start();
        } catch (SessionStartException $e) {
            //
        }
        
        $this->assertSame('value', $session->get('key'));        
        $this->assertFalse($session->has('key'));     
    }    
}
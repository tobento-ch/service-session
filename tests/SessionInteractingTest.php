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

/**
 * SessionInteractingTest tests
 */
class SessionInteractingTest extends TestCase
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
    
    public function testSetMethod()
    {
        $session = $this->createSession();
        
        $this->assertSame(
            $session,
            $session->set('key', 'value')
        );        
    }
    
    public function testGetMethod()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertSame(null, $session->get('foo'));
        $this->assertSame(null, $session->get('bar.foo'));
        
        $this->assertSame('value', $session->get('key'));
        
        // using dot notation:
        $this->assertSame('blue', $session->get('meta.color'));
        $this->assertSame(['color' => 'blue'], $session->get('meta'));

        // using a default value if key does not exist
        $this->assertSame('default', $session->get('foo', 'default'));
        $this->assertSame('default', $session->get('bar.foo', 'default'));
    }
    
    public function testHasMethod()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertFalse($session->has('foo'));
        $this->assertFalse($session->has('bar.foo'));
        
        $this->assertTrue($session->has('key'));
        
        // using dot notation:
        $this->assertTrue($session->has('meta.color'));
        $this->assertTrue($session->has('meta'));
    }
    
    public function testHasMethodWithMultipleKeys()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertFalse($session->has('foo', 'bar.foo'));
        
        $this->assertTrue($session->has('key', 'meta.color'));
        $this->assertFalse($session->has('key', 'meta.foo'));
    }

    public function testHasMethodWithMultipleKeysAsArray()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertFalse($session->has(['foo', 'bar.foo']));
        
        $this->assertTrue($session->has(['key', 'meta.color']));
        $this->assertFalse($session->has(['key', 'meta.foo']));
    } 
    
    public function testDeleteMethod()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertTrue($session->has('key'));
        $this->assertTrue($session->has('meta.color'));
        $this->assertTrue($session->has('meta'));
        
        $session->delete('key');
        $this->assertFalse($session->has('key'));
        
        $session->delete('meta.color');
        $this->assertFalse($session->has('meta.color'));
        $this->assertTrue($session->has('meta'));     
    } 
    
    public function testDeleteAllMethod()
    {
        $session = $this->createSession();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $session->deleteAll();
        
        $this->assertSame(['_session_expires' => null], $session->all());
    } 
    
    public function testAllMethod()
    {
        $session = $this->createSession();
        $session->deleteAll();
        
        $session->set('key', 'value');
        $session->set('meta.color', 'blue');
        
        $this->assertEquals(
            [
                '_session_expires' => null,
                'key' => 'value',
                'meta' => [
                    'color' => 'blue',
                ],
            ],
            $session->all()
        );
    }     
}
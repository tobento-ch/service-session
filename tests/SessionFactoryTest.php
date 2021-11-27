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
use Tobento\Service\Session\SessionFactory;
use Tobento\Service\Session\SessionFactoryInterface;
use Tobento\Service\Session\SessionInterface;

/**
 * SessionFactoryTest tests
 */
class SessionFactoryTest extends TestCase
{
    public function testThatImplementsSessionFactoryInterface()
    {
        $this->assertInstanceOf(
            SessionFactoryInterface::class,
            new SessionFactory()
        );
    }
    
    public function testCreateSessionMethod()
    {
        $session = (new SessionFactory())->createSession('name', [
            'maxlifetime' => 1800,
            'cookiePath' => '/',
            'cookieDomain' => '',
            'cookieSamesite' => 'Strict',
            'secure' => true,
            'httpOnly' => true,
            'saveHandler' => null,
        ]);
        
        $this->assertInstanceOf(
            SessionInterface::class,
            $session
        );      
    }
}
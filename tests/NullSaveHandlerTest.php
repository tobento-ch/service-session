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
use Tobento\Service\Session\NullSaveHandler;
use Tobento\Service\Session\SaveHandlerInterface;

/**
 * NullSaveHandlerTest tests
 */
class NullSaveHandlerTest extends TestCase
{    
    public function testThatImplementsSaveHandlerInterface()
    {
        $this->assertInstanceOf(
            SaveHandlerInterface::class,
            new NullSaveHandler()
        );
    }
    
    public function testCloseMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->close()
        );        
    }
    
    public function testDestroyMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->destroy('sessionid')
        );        
    }

    public function testGcMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->gc(5)
        );        
    }
    
    public function testOpenMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->open('path', 'name')
        );        
    }
    
    public function testReadMethod()
    {        
        $this->assertSame(
            '',
            (new NullSaveHandler())->read('sessionid')
        );        
    }
    
    public function testWriteMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->write('sessionid', 'data')
        );        
    }
    
    public function testRegisterShutdownMethod()
    {        
        $this->assertTrue(
            (new NullSaveHandler())->registerShutdown()
        );        
    }     
}
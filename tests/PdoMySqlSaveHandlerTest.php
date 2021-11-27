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
use Tobento\Service\Session\PdoMySqlSaveHandler;
use Tobento\Service\Session\SaveHandlerInterface;
use PDO;

/**
 * PdoMySqlSaveHandler tests
 */
class PdoMySqlSaveHandlerTest extends TestCase
{
    /**
     * @var null|PDO
     */
    protected null|PDO $pdo = null;
    
    protected function setUp(): void
    {
        if (! getenv('TEST_TOBENTO_SESSION_PDO_MYSQL')) {
            $this->markTestSkipped('PdoMyMysqlSaveHandler tests are disabled');
        }

        $this->pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_SESSION_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_SESSION_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_SESSION_PDO_MYSQL_PASSWORD'),
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
        
    protected function createTable(PDO $pdo, string $table): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS `'.$table.'` (`id` varchar(128) NOT NULL PRIMARY KEY, `data` text, `expiry` int(14) unsigned) ENGINE=InnoDB;');   
    }
    
    protected function dropTable(PDO $pdo, string $table): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `'.$table.'`;');
    }
    
    public function testThatImplementsSaveHandlerInterface()
    {
        $this->assertInstanceOf(
            SaveHandlerInterface::class,
            new PdoMySqlSaveHandler('session', $this->pdo)
        );
    }

    public function testCloseMethod()
    {        
        $this->assertTrue(
            (new PdoMySqlSaveHandler('session', $this->pdo))->close()
        );        
    }
    
    public function testDestroyMethod()
    {
        $saveHandler = new PdoMySqlSaveHandler('session', $this->pdo);
        
        $this->createTable($saveHandler->pdo(), $saveHandler->table());
        
        $this->assertTrue($saveHandler->open('path', 'name'));
        
        $data = ['foo' => 'bar'];
        
        $this->assertTrue($saveHandler->write('100', serialize($data)));
        
        $saveHandler->exists(false);
        
        $this->assertTrue($saveHandler->write('101', serialize($data)));
        
        $this->assertTrue($saveHandler->destroy('100'));
        $this->assertSame('', $saveHandler->read('100'));
        
        $this->assertSame(['foo' => 'bar'], unserialize($saveHandler->read('101')));
        
        $this->assertTrue($saveHandler->destroy('102'));
        
        $this->dropTable($saveHandler->pdo(), $saveHandler->table());
    }
    
    public function testGcMethod()
    {
        $saveHandler = new PdoMySqlSaveHandler('session', $this->pdo);
        
        $this->createTable($saveHandler->pdo(), $saveHandler->table());
        
        $this->assertTrue($saveHandler->open('path', 'name'));
        
        $data = ['foo' => 'bar'];
        
        $this->assertTrue($saveHandler->write('100', serialize($data)));       
        $saveHandler->exists(false);
        
        sleep(1);
        
        $this->assertTrue($saveHandler->write('101', serialize($data)));
        $saveHandler->exists(false);
        
        $saveHandler->gc(5);
        
        $this->assertSame(['foo' => 'bar'], unserialize($saveHandler->read('100')));
        $this->assertSame(['foo' => 'bar'], unserialize($saveHandler->read('101')));
        
        $saveHandler->gc(1);
        
        $this->assertSame('', $saveHandler->read('100'));
        $this->assertSame(['foo' => 'bar'], unserialize($saveHandler->read('101')));
        
        $this->dropTable($saveHandler->pdo(), $saveHandler->table());
    }    
    
    public function testReadWrite()
    {
        $saveHandler = new PdoMySqlSaveHandler('session', $this->pdo);
        
        $this->createTable($saveHandler->pdo(), $saveHandler->table());
        
        $this->assertTrue($saveHandler->open('path', 'name'));
        
        $id = '123';
        $data = ['foo' => 'bar', 'bar' => ['foo' => 'bar'], 'num' => [1,2,3]];
        
        $this->assertTrue($saveHandler->write($id, serialize($data)));      
        $this->assertEquals($data, unserialize($saveHandler->read($id)));
        
        $this->dropTable($saveHandler->pdo(), $saveHandler->table());
    }
    
    public function testReadReturnsString()
    {
        $saveHandler = new PdoMySqlSaveHandler('session', $this->pdo);
        
        $this->createTable($saveHandler->pdo(), $saveHandler->table());
        
        $this->assertTrue($saveHandler->open('path', 'name'));
   
        $this->assertTrue(is_string($saveHandler->read('123')));
        
        $this->dropTable($saveHandler->pdo(), $saveHandler->table());
    }

    public function testRegisterShutdownMethod()
    {        
        $this->assertTrue(
            (new PdoMySqlSaveHandler('session', $this->pdo))->registerShutdown()
        );
    }   
}
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

namespace Tobento\Service\Session;

use Tobento\Service\Session\SaveHandlerInterface;
use Tobento\Service\Session\ExistenceAwareInterface;
use PDO;
use RuntimeException;
use ReturnTypeWillChange;

/**
 * PdoMySqlSaveHandler
 */
class PdoMySqlSaveHandler implements SaveHandlerInterface, ExistenceAwareInterface
{
    /**
     * If the session exists.
     *
     * @var bool
     */
    protected bool $exists = false;

    /**
     * Create a new PdoSaveHandler.
     *
     * @param string $table     
     * @param PDO $pdo
     */
    public function __construct(
        protected string $table,
        protected PDO $pdo,
    ) {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            throw new RuntimeException('Supporting only mysql PDO driver');
        }
    }
    
    /**
     * Closes the current session. This function is automatically executed
     * when closing the session, or explicitly via session_write_close().
     *
     * @return bool The return value (usually true on success, false on failure).
     *              Note this value is returned internally to PHP for processing.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroys a session. Called by session_regenerate_id() (with $destroy = true),
     * session_destroy() and when session_decode() fails. 
     *
     * @param string $id The session ID being destroyed.
     * @return bool The return value (usually true on success, false on failure).
     */
    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM '.$this->backtickValue($this->table).' WHERE id = ?'
        );

        $statement->execute([$id]);

        return true;
    }

    /**
     * Cleans up expired sessions. Called by session_start(),
     * based on session.gc_divisor, session.gc_probability and
     * session.gc_maxlifetime settings.
     *
     * @param int $maxlifetime
     * @return int|false Returns the number of deleted sessions on success.
     *
     * @psalm-suppress all
     */
    #[ReturnTypeWillChange]
    public function gc(int $maxlifetime)
    {
        $past = time() - $maxlifetime;
        
        $statement = $this->pdo->prepare(
            'DELETE FROM '.$this->backtickValue($this->table).' WHERE expiry <= ?'
        );
        
        $statement->execute([$past]);

        return true;
    }
    
    /**
     * Re-initialize existing session, or creates a new one.
     * Called when a session starts or when session_start() is invoked.
     *
     * @param string $path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Reads the session data from the session storage, and returns the results.
     *
     * @param string $id The session id.
     * @return string|false Returns an encoded string of the read data.
     *                      If nothing was read, it must return false.
     */
    #[ReturnTypeWillChange]
    public function read(string $id)
    {
        $statement = $this->pdo->prepare(
            'SELECT data FROM '.$this->backtickValue($this->table).' WHERE id = ?'
        );

        $statement->execute([$id]);

        $data = $statement->fetch();

        if (isset($data['data']))
        {
            $this->exists = true;
            return $data['data'];
        }

        return '';
    }

    /**
     * Writes the session data to the session storage.
     * Called by session_write_close(),
     * when session_register_shutdown() fails, or during a normal shutdown.
     *
     * @param string $id The session id.
     * @param string $data The encoded session data.
     * @return bool The return value (usually true on success, false on failure)
     */
    public function write(string $id, string $data): bool
    {
        if (! $this->exists) {
            $this->read($id);
        }
        
        if ($this->exists) {
            $this->performUpdate($id, $data);
        } else {
            $this->performInsert($id, $data);
        }
                        
        return $this->exists = true;    
    }
    
    /**
     * Returns true if to register shutdown, otherwise false.
     *
     * @return bool
     */
    public function registerShutdown(): bool
    {
        return true;    
    }    
    
    /**
     * Set if the session entry exists.
     *
     * @param bool $exists
     * @return static $this
     */
    public function exists(bool $exists): static
    {
        $this->exists = $exists;
        return $this;
    }
    
    /**
     * Returns the table name.
     *
     * @return string
     */
    public function table(): string
    {
        return $this->table;
    }
    
    /**
     * Returns the pdo.
     *
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }    

    /**
     * Perform session insert.
     *
     * @param string $id The session id.
     * @param string $data The encoded session data.
     * @return void
     */
    protected function performInsert(string $id, string $data): void
    {
        $expiry = time();

        $statement = $this->pdo->prepare(
            'INSERT INTO '.$this->backtickValue($this->table).' (id, data, expiry) VALUES (?, ?, ?)'
        );

        $statement->execute([$id, $data, $expiry]);
    }
    
    /**
     * Perform session update.
     *
     * @param string $id The session id.
     * @param string $data The encoded session data.
     * @return void
     */
    protected function performUpdate(string $id, string $data): void
    {
        $expiry = time();

        $statement = $this->pdo->prepare(
            'UPDATE '.$this->backtickValue($this->table).' SET data = ?, expiry = ? WHERE id = ?'
        );

        $statement->execute([$data, $expiry, $id]);
    }
    
    /**
     * Backtick value.
     *
     * @param string $value
     * @return string
     */    
    protected function backtickValue(string $value): string
    {
        return '`'.$value.'`';
    }
}
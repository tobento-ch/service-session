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
use ReturnTypeWillChange;

/**
 * NullSaveHandler
 */
class NullSaveHandler implements SaveHandlerInterface
{
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
        return true;    
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
}
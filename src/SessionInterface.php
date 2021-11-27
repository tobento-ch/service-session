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

/**
 * SessionInterface
 */
interface SessionInterface
{
    /**
     * Returns the session name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Returns the session id if exists, otherwise null.
     *
     * @return null|string
     */
    public function id(): null|string;
    
    /**
     * Starts the session.
     *
     * @param string $name A session name.
     * @return static $this
     * @throws SessionStartException
     * @throws SessionExpiredException
     * @throws SessionValidationException
     */
    public function start(): static;
    
    /**
     * Save the session data to storage.
     *
     * @return static $this
     * @throws SessionSaveException
     */    
    public function save(): static;
    
    /*
     * Destroys session altogether but does not regenerate its id.
     *
     * @return static $this
     */
    public function destroy(): static;
    
    /**
     * Regenerates new session id. 
     * Use it when changing important user states such as log in, logout.
     *
     * @param bool $deleteOldSession Whether to delete the old associated session file or not. 
     * @return static $this
     */
    public function regenerateId(bool $deleteOldSession = true): static;
    
    /**
     * Set session data by key.
     * 
     * @param string $key The key.
     * @param mixed $value The value.
     * @return static $this
     */
    public function set(string $key, mixed $value): static;
                    
    /**
     * Returns session data by key.
     *
     * @param string $key The key.
     * @param mixed $default A default value.
     * @return mixed The value or the default value if not exist.
     */
    public function get(string $key, mixed $default = null): mixed;
        
    /**
     * Returns true if session data exists, otherwise false.
     *
     * @param mixed $key The key.
     * @return bool True if exist, else false.
     */
    public function has(mixed $key): bool;
    
    /**
     * Delete session data by key.
     *
     * @param string $key The key.
     * @return static $this
     */
    public function delete(string $key): static;

    /**
     * Delete all session data.
     *
     * @return static $this
     */
    public function deleteAll(): static;
    
    /**
     * Returns all session data.
     *
     * @return array
     */
    public function all(): array;
    
    /**
     * Set session flash data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function flash(string $key, mixed $value): static;
    
    /**
     * Set session flash now data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function now(string $key, mixed $value): static;
    
    /**
     * Set session flash once data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function once(string $key, mixed $value): static;    
}
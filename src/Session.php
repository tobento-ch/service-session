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

use Tobento\Service\Collection\Arr;

use PHP_SESSION_ACTIVE;

/**
 * Session
 */
class Session implements SessionInterface
{
    /**
     * The key to save the session expires data.
     */
    protected const EXPIRES_KEY = '_session_expires';
    
    /**
     * The key to save the session flash data.
     */
    protected const FLASH_KEY = '_session_flash';
    
    /**
     * The key to save the session once flash data.
     */
    protected const FLASH_ONCE_KEY = '_session_flash_once';    
    
    /**
     * @var null|string The session id.
     */
    protected null|string $id = null;
    
    /**
     * @var array<int, string>
     */
    protected array $nowKeys = [];
    
    /**
     * @var array<string, bool>
     */
    protected array $onceKeys = [];
    
    /**
     * @var bool
     */
    protected bool $isClosed = false;
    
    /**
     * Create a new Session.
     *
     * @param string $name The session name.
     * @param int $maxlifetime The duration in seconds until the session will expire.
     * @param null|string $cookiePath
     * @param null|string $cookieDomain
     * @param string $cookieSamesite
     * @param null|bool $secure
     * @param null|bool $httpOnly
     * @param null|SaveHandlerInterface $saveHandler
     * @param null|ValidationInterface $validation
     */
    public function __construct(
        protected string $name,
        protected int $maxlifetime = 1800,
        protected null|string $cookiePath = '/',
        protected null|string $cookieDomain = null,
        protected string $cookieSamesite = 'Strict',
        protected null|bool $secure = true,
        protected null|bool $httpOnly = true,
        protected null|SaveHandlerInterface $saveHandler = null,
        protected null|ValidationInterface $validation = null,
    ) {
        if (!is_null($saveHandler)) {
            session_set_save_handler($saveHandler, $saveHandler->registerShutdown());
        }
        
        if (!in_array($cookieSamesite, ['Lax', 'Strict', 'None'])) {
            $this->cookieSamesite = 'Strict';
        }
    }

    /**
     * Returns the session name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the session id if exists, otherwise null.
     *
     * @return null|string
     */
    public function id(): null|string
    {
        return $this->id;
    }
    
    /**
     * Starts the session.
     *
     * @param string $name A session name.
     * @return static $this
     * @throws SessionStartException
     * @throws SessionExpiredException
     * @throws SessionValidationException
     */
    public function start(): static
    {
        if (! preg_match('/^[a-zA-Z0-9]+$/', $this->name)) {
            throw new SessionStartException(
                'Session name must be alphanumeric only'
            );
        }
        
        $this->startSession($this->name);
        
        return $this;
    }
    
    /**
     * Save the session data to storage.
     *
     * @return static $this
     * @throws SessionSaveException
     */    
    public function save(): static
    {
        $this->flashing();
        
        if ($this->isClosed) {
            return $this;
        }
        
        $this->isClosed = true;
        
        if (
            session_write_close() === false
            && !is_null($this->saveHandler)
        ) {
            throw new SessionSaveException(
                'Write session data and end session failed'
            );            
        }
        
        return $this;
    }
    
    /*
     * Destroys session altogether but does not regenerate its id.
     *
     * @return static $this
     */
    public function destroy(): static
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $this;
        }
        
        if (isset($_COOKIE[$this->name]))
        {
            setcookie($this->name, '', [
                'expires' => time() - 42000,
                'path' => $this->cookiePath,
                'domain' => $this->cookieDomain
            ]);
        }
        
        $_SESSION = [];
        session_destroy();
        return $this;
    }
    
    /**
     * Regenerates new session id. 
     * Use it when changing important user states such as log in, logout.
     *
     * @param bool $deleteOldSession Whether to delete the old associated session file or not. 
     * @return static $this
     * @throws SessionException
     */
    public function regenerateId(bool $deleteOldSession = true): static
    {
        if (!is_null($this->saveHandler)) {
            if ($this->saveHandler instanceof ExistenceAwareInterface) {
                $this->saveHandler->exists(false);
            }
        }
        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new SessionException(
                'Session ID cannot be regenerated when there is no active session'
            );
        }        
                
        session_regenerate_id($deleteOldSession);
        
        $this->id = session_id();
        
        return $this;
    }    
    
    /**
     * Set session data by key.
     * 
     * @param string $key The key.
     * @param mixed $value The value.
     * @return static $this
     */
    public function set(string $key, mixed $value): static
    {
        $_SESSION = Arr::set($this->all(), $key, $value);

        return $this;
    } 
                    
    /**
     * Returns session data by key.
     *
     * @param string $key The key.
     * @param mixed $default A default value.
     * @return mixed The value or the default value if not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {        
        $data = Arr::get($this->all(), $key, $default);
        
        // we would need to store once keys in session.        
        if (isset($this->onceKeys[$key])) {
            $this->delete($key);
            unset($this->onceKeys[$key]);
        }
        
        return $data;
    }
        
    /**
     * Returns true if session data exists, otherwise false.
     *
     * @param mixed $key The key.
     * @return bool True if exist, else false.
     */
    public function has(mixed $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();
        
        foreach($keys as $key)
        {
            if (! Arr::has($this->all(), $key)) {
                return false;
            }            
        }
        
        return true;
    }
    
    /**
     * Delete session data by key.
     *
     * @param string $key The key.
     * @return static $this
     */
    public function delete(string $key): static
    {
        $_SESSION = Arr::delete($this->all(), $key);
        
        return $this;
    }

    /**
     * Delete all session data.
     *
     * @return static $this
     */
    public function deleteAll(): static
    {
        // We will need to keep the current expires data synced,
        // otherwise you will not get expired after waiting a long
        // time as it will create new timestamp in isExpired() method.
        
        $expires = $this->get(self::EXPIRES_KEY);
        
        $_SESSION = [];
        
        $this->set(self::EXPIRES_KEY, $expires);
        
        return $this;
    }
    
    /**
     * Returns all session data.
     *
     * @return array
     */
    public function all(): array
    {
        return $_SESSION ?? [];
    }
    
    /**
     * Set session flash data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function flash(string $key, mixed $value): static
    {
        $this->set($key, $value);
                    
        // store the key as to know which to clear after flushing.
        $flashNew = $this->get(self::FLASH_KEY.'.new');
        $flashNew = is_array($flashNew) ? $flashNew : [];
        $flashNew[] = $key;
        $flashNew = array_unique($flashNew);
        $this->set(self::FLASH_KEY.'.new', $flashNew);
        
        // remove
        $this->set(
            self::FLASH_KEY.'.old',
            array_diff($this->get(self::FLASH_KEY.'.old', []), [$key])
        );
        
        return $this;
    }

    /**
     * Set session flash now data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function now(string $key, mixed $value): static
    {
        $this->set($key, $value);
        
        // store key as to delete on flashing method.
        $this->nowKeys[] = $key;
        return $this;
    }
    
    /**
     * Set session flash once data by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static $this
     */
    public function once(string $key, mixed $value): static
    {
        $this->set($key, $value);
        
        // store key as to delete on get method.        
        $this->onceKeys[$key] = true;
        return $this;
    }    
    
    /**
     * Start session.
     *
     * @param string $sessionName The session name.
     * @return void
     * @throws SessionStartException
     * @throws SessionExpiredException
     * @throws SessionValidationException
     */
    protected function startSession(string $sessionName)
    {
        // Check if session is already started.
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        if (headers_sent()) {
            if (is_null($this->id())) {
                throw new SessionStartException(
                    'Failed to start the session as headers sent'
                );
            } else {
                return;
            }
        }        
        
        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', '1');
        
        // Get session cookie parameters.
        $cookieParams = session_get_cookie_params();
        
        // Set the cookie parameters.
        // we could set the $this->maxlifetime here to expire the session. But we do it by our own way.
        session_set_cookie_params([
            'domain' => $this->cookieDomain,
            'httponly' => $this->httpOnly,
            'lifetime' => $cookieParams['lifetime'],
            'path' => $this->cookiePath,
            'secure' => $this->secure,
            'samesite' => $this->cookieSamesite
        ]);
        
        // Sync the session maxlifetime.
        ini_set('session.gc_maxlifetime', (string) $this->maxlifetime);        
            
        // Registers session_write_close() as a shutdown function.
        // @see http://php.net/manual/en/function.session-register-shutdown.php
        session_register_shutdown();
        
        // Change the session name.
        session_name($sessionName);
        
        // Now we can start the session.
        if (!session_start()) {
            throw new SessionStartException('Failed to start the session');
        }
        
        // Get and/or set the current session id.
        $sessionId = session_id();
        
        if (!$this->isValidSessionId($sessionId)) {
            throw new SessionStartException('Invalid session id');
        }
        
        $this->id = $sessionId;
        
        // Check if session has expired.
        if ($this->isExpired()) {
            throw new SessionExpiredException('Session expired');
        }
        
        // process the validation if any.
        if (!is_null($this->validation)) {
            $this->validation->process($this);
        }        
        
        // Handle once flash.
        $this->onceKeys = $this->get(self::FLASH_ONCE_KEY, []);
    }
    
    /**
     * Check if session has expired sets expired state if it has expired.
     *
     * @return bool
     */
    protected function isExpired(): bool
    {
        if (! $this->has(self::EXPIRES_KEY)) {
            $this->set(self::EXPIRES_KEY, time()+$this->maxlifetime);
        }
        
        if ($this->get(self::EXPIRES_KEY) > time()) {
            $this->set(self::EXPIRES_KEY, time()+$this->maxlifetime);
            return false;
        }

        return true;
    }
    
    /**
     * Flashing the flash data.
     *
     * @return void
     */    
    protected function flashing(): void
    {
        // handle once flash data.
        $this->set(self::FLASH_ONCE_KEY, $this->onceKeys);
        
        // handle now flash data.
        foreach($this->nowKeys as $key) {
            $this->delete($key);
        }
        
        $this->nowKeys = [];
        
        // handle flash data.
        $oldFlashData = $this->get(self::FLASH_KEY.'.old');
        
        if (is_array($oldFlashData)) {
            foreach($oldFlashData as $flashKey) {
                $this->delete($flashKey);
            }
        }

        $this->set(
            self::FLASH_KEY.'.old',
            $this->get(self::FLASH_KEY.'.new', [])
        );
        
        $this->set(self::FLASH_KEY.'.new', []);
    }
    
    /**
     * Returns true if session id is valid, otherwise false.
     *
     * @param mixed $id
     * @return bool
     */    
    protected function isValidSessionId(mixed $id): bool
    {
        if (!is_string($id) || empty($id)) {
            return false;
        }
        
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id) !== false;
    }
}
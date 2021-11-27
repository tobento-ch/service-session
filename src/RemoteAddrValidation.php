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
 * RemoteAddrValidation
 */
class RemoteAddrValidation implements ValidationInterface
{
    /**
     * Create a new RemoteAddrValidation.
     *
     * @param mixed $remoteAddr The remote addr to validate.
     * @param null|array $trustedProxies
     * @param string $remoteAddrKey The ip address key used for storing to session.
     */
    public function __construct(
        protected mixed $remoteAddr,
        protected null|array $trustedProxies = null,
        protected string $remoteAddrKey = '_session_remoteAddr',
    ) {}
    
    /**
     * Process the validation.
     *
     * @param SessionInterface $session
     * @return void
     * @throws SessionValidationException
     */
    public function process(SessionInterface $session): void
    {
        if (
            !is_null($this->trustedProxies)
            && in_array($this->remoteAddr, $this->trustedProxies)
        ) {
            return;
        }
        
        // verify ip address.
        if (! filter_var($this->remoteAddr, FILTER_VALIDATE_IP))
        {
            throw new SessionValidationException(
                $this,
                'Invalid remote address'
            );
        }
        
        if ($this->remoteAddr !== $session->get($this->remoteAddrKey, $this->remoteAddr))
        {
            throw new SessionValidationException(
                $this,
                'Session remote address validation failed'
            );
        }
        
        $session->set($this->remoteAddrKey, $this->remoteAddr);
    }

    /**
     * Returns the remote addr to validate.
     *
     * @return mixed
     */
    public function remoteAddr(): mixed
    {        
        return $this->remoteAddr;
    }
    
    /**
     * Returns the trusted proxies.
     *
     * @return null|array
     */
    public function trustedProxies(): null|array
    {        
        return $this->trustedProxies;
    }
}
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
 * HttpUserAgentValidation
 */
class HttpUserAgentValidation implements ValidationInterface
{
    /**
     * Create a new HttpUserAgentValidation.
     *
     * @param mixed $httpUserAgent The http user agent to validate.
     * @param string $httpUserAgentKey The http user agent key used for storing to session.
     */
    public function __construct(
        protected mixed $httpUserAgent,
        protected string $httpUserAgentKey = '_session_httpUserAgent',
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
        if ($this->httpUserAgent !== $session->get($this->httpUserAgentKey, $this->httpUserAgent))
        {
            throw new SessionValidationException(
                $this,
                'Session http user agent validation failed'
            );
        }
        
        $session->set($this->httpUserAgentKey, $this->httpUserAgent);
    }
    
    /**
     * Returns the http user agent to validate.
     *
     * @return mixed
     */
    public function httpUserAgent(): mixed
    {        
        return $this->httpUserAgent;
    }
}
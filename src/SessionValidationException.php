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

use Exception;
use Throwable;

/**
 * SessionValidationException
 */
class SessionValidationException extends SessionException
{
    /**
     * Create a new SessionValidationException.
     *
     * @param ValidationInterface $validation
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected ValidationInterface $validation,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Get the validation.
     *
     * @return ValidationInterface
     */
    public function validation(): ValidationInterface
    {
        return $this->validation;
    }    
}
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
 * ValidationInterface
 */
interface ValidationInterface
{
    /**
     * Process the validation.
     *
     * @param SessionInterface $session
     * @return void
     * @throws SessionValidationException
     */
    public function process(SessionInterface $session): void;
}
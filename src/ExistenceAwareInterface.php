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
 * ExistenceAwareInterface
 */
interface ExistenceAwareInterface
{
    /**
     * Set if the session entry exists.
     *
     * @param bool $exists
     * @return static $this
     */
    public function exists(bool $exists): static;
}
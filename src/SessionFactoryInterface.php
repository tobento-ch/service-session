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
 * SessionFactoryInterface
 */
interface SessionFactoryInterface
{
    /**
     * Create a new Session.
     *
     * @param string $name
     * @param array $config
     * @return SessionInterface
     */
    public function createSession(string $name, array $config = []): SessionInterface;
}
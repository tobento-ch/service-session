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

use Tobento\Service\Collection\Collection;

/**
 * SessionFactory
 */
class SessionFactory implements SessionFactoryInterface
{    
    /**
     * Create a new Session.
     *
     * @param string $name
     * @param array $config
     * @return SessionInterface
     */
    public function createSession(string $name, array $config = []): SessionInterface
    {
        $config = new Collection($config);
        
        $saveHandler = null;
        
        if ($config->get('saveHandler') instanceof SaveHandlerInterface) {
            $saveHandler = $config->get('saveHandler');
        }
        
        $validation = null;
        
        if ($config->get('validation') instanceof ValidationInterface) {
            $validation = $config->get('validation');
        }        
        
        return new Session(
            name: $name,
            maxlifetime: $config->get('maxlifetime', 1800),
            cookiePath: $config->get('cookiePath', '/'),
            cookieDomain: $config->get('cookieDomain', ''),
            cookieSamesite: $config->get('cookieSamesite', 'Strict'),
            secure: $config->get('secure', true),
            httpOnly: $config->get('httpOnly', true),
            saveHandler: $saveHandler,
            validation: $validation,
        );
    }
}
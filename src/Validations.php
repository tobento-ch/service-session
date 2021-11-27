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
 * Validations
 */
class Validations implements ValidationInterface
{
    /**
     * @var array<int, ValidationInterface>
     */
    protected array $validations = [];
    
    /**
     * Create a new Validations.
     *
     * @param ValidationInterface $validation
     */
    public function __construct(
        ValidationInterface ...$validation,
    ) {
        $this->validations = $validation;
    }

    /**
     * Process the validation.
     *
     * @param SessionInterface $session
     * @return void
     * @throws SessionValidationException
     */
    public function process(SessionInterface $session): void
    {
        foreach($this->validations as $validation)
        {
            $validation->process($session);
        }
    }
    
    /**
     * Returns the validations.
     *
     * @return array<int, ValidationInterface>
     */
    public function validations(): array
    {        
        return $this->validations;
    }    
}
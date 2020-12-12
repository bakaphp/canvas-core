<?php

declare(strict_types=1);

namespace Canvas\Contracts;

/**
 * Trait TokenTrait.
 *
 * @package Niden\Traits
 */
trait RequestJwtTrait
{
    /**
    * @return string
    */
    public function getBearerTokenFromHeader(): string
    {
        return str_replace('Bearer ', '', $this->getHeader('Authorization'));
    }

    /**
     * @return bool
     */
    public function isEmptyBearerToken(): bool
    {
        return empty($this->getBearerTokenFromHeader());
    }

    abstract public function getHeader($header);
}

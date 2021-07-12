<?php

declare(strict_types=1);

namespace Canvas\Contracts;

trait RequestJwtTrait
{
    /**
     * @return string
     */
    public function getBearerTokenFromHeader() : string
    {
        return str_replace(['Bearer ', 'Authorization'], '', $this->getHeader('Authorization'));
    }

    /**
     * @return bool
     */
    public function isEmptyBearerToken() : bool
    {
        return empty($this->getBearerTokenFromHeader());
    }

    abstract public function getHeader(string $header) : string;
}

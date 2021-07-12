<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Phalcon\Di;

class Jwt
{
    /**
     * Get the JWT Configuration.
     *
     * @return Configuration
     */
    public static function getConfig() : Configuration
    {
        return  Configuration::forSymmetricSigner(
            // You may use any HMAC variations (256, 384, and 512)
            new Sha512(),
            InMemory::plainText(Di::getDefault()->get('config')->jwt->secretKey)
        );
    }
}

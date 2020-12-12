<?php

declare(strict_types=1);

namespace Canvas\Contracts\Auth;

use Canvas\Auth\Jwt;
use DateTimeImmutable;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\ValidationData;
use Phalcon\Di;
use Phalcon\Security\Random;

trait TokenTrait
{
    /**
     * Returns the string token.
     *
     * @return string
     *
     * @throws ModelException
     */
    public function getToken() : array
    {
        $random = new Random();
        $sessionId = $random->uuid();

        $token = self::createJwtToken($sessionId, $this->getEmail());
        $refreshToken = self::createJwtToken($sessionId, $this->getEmail());

        return [
            'sessionId' => $sessionId,
            'token' => $token['token'],
            'refresh_token' => $refreshToken['token']
        ];
    }

    /**
     * Returns the ValidationData object for this record (JWT).
     *
     * @return bool
     *
     * @deprecated 0.2
     */
    public static function getValidationData(Token $token) : bool
    {
        return self::validateJwtToken($token);
    }

    /**
     * Given a JWT token validate it.
     *
     * @param Token $token
     *
     * @throws RequiredConstraintsViolated
     * @throws NoConstraintsGiven
     *
     * @return bool
     */
    public static function validateJwtToken(Token $token) : bool
    {
        $config = Jwt::getConfig();

        return $config->validator()->validate(
            $token,
            new IssuedBy(getenv('TOKEN_AUDIENCE')),
            new SignedWith($config->signer(), $config->verificationKey())
        );
    }

    /**
     * Create a new session based off the refresh token session id.
     *
     * @param string $sessionId
     * @param string $email
     *
     * @return array
     */
    public static function createJwtToken(string $sessionId, string $email) : array
    {
        $now = new DateTimeImmutable();
        $config = Jwt::getConfig();
        //get the expiration in hours
        $expiration = ceil((Di::getDefault()->get('config')->jwt->payload->exp ?? 604800) / 3600);

        //https://lcobucci-jwt.readthedocs.io/en/latest/issuing-tokens/
        $token = $config->builder()
                ->issuedBy(getenv('TOKEN_AUDIENCE'))
                ->permittedFor(getenv('TOKEN_AUDIENCE'))
                ->identifiedBy($sessionId)
                ->issuedAt($now)
                ->canOnlyBeUsedAfter($now->modify('+10 minute'))
                ->expiresAt($now->modify('+' . $expiration . ' hour'))
                ->withClaim('sessionId', $sessionId)
                ->withClaim('email', $email)
                // Builds a new token
                ->getToken($config->signer(), $config->signingKey());

        return [
            'sessionId' => $sessionId,
            'token' => $token->toString()
        ];
    }
}

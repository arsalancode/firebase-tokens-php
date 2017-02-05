<?php

namespace Firebase\Auth\Token;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;

final class Generator implements Domain\Generator
{
    /**
     * @var string
     */
    private $clientEmail;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Signer
     */
    private $signer;

    public function __construct(
        string $clientEmail,
        string $privateKey,
        Signer $signer = null
    ) {
        $this->clientEmail = $clientEmail;
        $this->privateKey = $privateKey;

        $this->builder = $this->createBuilder();
        $this->signer = $signer ?: new Sha256();
    }

    /**
     * Returns a token for the given user and claims.
     *
     * @param mixed $uid
     * @param array $claims
     *
     * @return Token
     */
    public function createCustomToken($uid, array $claims = []): Token
    {
        foreach ($claims as $key => $value) {
            $this->builder->set($key, $value);
        }
        $this->builder->set('claims', $claims);

        $this->builder->set('uid', (string) $uid);

        $now = time();

        return $this->builder
            ->setHeader('debug', true)
            ->setIssuedAt($now)
            ->setExpiration($now + (60 * 60))
            ->sign($this->signer, $this->privateKey)
            ->getToken();
    }

    private function createBuilder(): Builder
    {
        return (new Builder())
            ->setIssuer($this->clientEmail)
            ->setSubject($this->clientEmail)
            ->setAudience('https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit');
    }
}

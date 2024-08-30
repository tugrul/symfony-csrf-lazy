<?php

namespace App\Service;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class CsrfLazyTokenManager extends CsrfTokenManager
{
    protected bool $lazy = false;

    public function isLazy(): bool
    {
        return $this->lazy;
    }

    public function setLazy(bool $lazy): CsrfLazyTokenManager
    {
        $this->lazy = $lazy;

        return $this;
    }

    public function getToken(string $tokenId): CsrfToken
    {
        if ($this->isLazy()) {
            return new CsrfToken($tokenId, '');
        }

        return parent::getToken($tokenId);
    }
}

<?php

declare(strict_types=1);

namespace Canvas\Contracts\Auth;

interface UserInterface
{
    /**
     * Get the current Company the user is accessing
     *
     * @return integer
     */
    public function currentCompanyId() : int;
}
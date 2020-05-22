<?php
declare(strict_types=1);

namespace Canvas\Contracts\Notifications;

use Canvas\Contracts\Auth\AuthenticatableInterface;

interface NotificationInterface
{
    /**
     * Undocumented function.
     *
     * @return void
     */
    public function message() : string;

    public function process() : bool;

    public function trigger() : bool;

    public function setTo(AuthenticatableInterface $user) : void;

    public function setFrom(AuthenticatableInterface $user) : void;
}

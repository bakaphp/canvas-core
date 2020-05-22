<?php
declare(strict_types=1);

namespace Canvas\Contracts\Notifications;

use Canvas\Contracts\Auth\UserInterface;

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

    public function setTo(UserInterface $user) : void;

    public function setFrom(UserInterface $user) : void;
}

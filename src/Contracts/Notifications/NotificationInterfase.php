<?php
declare(strict_types=1);

namespace Canvas\Contracts\Notifications;

use Canvas\Models\Users;

interface NotificationInterfase
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function message(): string;

    public function process(): bool;

    public function trigger(): bool;
    
    public function setTo(Users $user): void;

    public function setFrom(Users $user): void;

}
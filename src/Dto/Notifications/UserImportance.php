<?php

namespace Canvas\Dto\Notifications;

class UserImportance
{
    public string $entity_id;
    public array $system_modules = [];
    public int $importance_id = 0;
}

<?php
declare(strict_types=1);

namespace Canvas\Models;

class Locales extends AbstractModel
{
    public string $name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('locales');
    }
}

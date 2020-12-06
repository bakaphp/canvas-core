<?php
declare(strict_types=1);

namespace Canvas\Models\Locations;

use Canvas\Models\AbstractModel;

class Countries extends AbstractModel
{
    public string $name;
    public ?string $flag = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('countries');
    }
}

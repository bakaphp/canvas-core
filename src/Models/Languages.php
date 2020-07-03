<?php
declare(strict_types=1);

namespace Canvas\Models;

class Languages extends AbstractModel
{
    public string $name;
    public string $title;
    public int $order;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('languages');
    }
}

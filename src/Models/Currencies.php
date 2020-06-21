<?php
declare(strict_types=1);

namespace Canvas\Models;

class Currencies extends AbstractModel
{
    public string $country;
    public ?string $currency = null;
    public ?string $code = null;
    public ?string $symbol = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('currencies');
    }
}

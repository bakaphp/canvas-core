<?php
declare(strict_types=1);

namespace Canvas\Models;

class Currencies extends AbstractModel
{
    public string $country;
    public string $currency;
    public string $code;
    public string $symbol;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('currencies');
    }
}

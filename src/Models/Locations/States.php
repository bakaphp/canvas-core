<?php
declare(strict_types=1);

namespace Canvas\Models\Locations;

use Canvas\Models\AbstractModel;

class States extends AbstractModel
{
    public int $countries_id;
    public string $name;
    public string $code;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('countries_states');
        $this->belongsTo('countries_id', Countries::class, 'id', ['alias' => 'countries']);
    }
}

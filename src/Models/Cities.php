<?php
declare(strict_types=1);

namespace Canvas\Models;

class Cities extends AbstractModel
{
    public int $countries_id;
    public int $states_id;
    public string $name;
    public float $latitude;
    public float $longitude;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('countries_cities');
        $this->belongsTo('countries_id', Countries::class, 'id', ['alias' => 'countries']);
        $this->belongsTo('states_id', Cities::class, 'id', ['alias' => 'cities']);
    }
}

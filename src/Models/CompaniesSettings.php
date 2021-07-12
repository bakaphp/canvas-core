<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;

class CompaniesSettings extends Model
{
    public int $companies_id;
    public string $name;
    public string $value;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->setSource('companies_settings');
    }
}

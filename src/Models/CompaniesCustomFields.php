<?php
declare(strict_types=1);

namespace Canvas\Models;

class CompaniesCustomFields extends AbstractModel
{
    public int $companies_id;
    public int $custom_fields_id;
    public ?string $value = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_custom_fields');

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'custom_fields_id',
            'Canvas\CustomFields\CustomFields',
            'id',
            ['alias' => 'fields']
        );
    }

    /**
     * Set the custom primary field id.
     *
     * @param int $id
     */
    public function setCustomId(int $id)
    {
        $this->companies_id = $id;
    }
}

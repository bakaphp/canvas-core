<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Class CompanyBranches.
 *
 * @package Canvas\Models
 *
 */
class CompaniesBranches extends AbstractModel
{
    public string $name;
    public ?string $address = null;
    public ?string $email = null;
    public ?string $zipcode = null;
    public ?string $phone = null;
    public int $companies_id;
    public int $users_id;
    public int $is_default;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_branches');

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'id']
        );
    }

    /**
     * Model validation.
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'name',
            new PresenceOf([
                'model' => $this,
                'required' => true,
            ])
        );

        return $this->validate($validator);
    }
}

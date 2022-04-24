<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Cashier\Billable;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class CompaniesBranches extends AbstractModel
{
    use Billable;

    public string $name;
    public ?string $stripe_id = null;
    public ?string $address = null;
    public ?string $email = null;
    public ?string $zipcode = null;
    public ?string $phone = null;
    public int $companies_id;
    public int $users_id;
    public int $is_default = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_branches');

        $this->belongsTo(
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'users_id',
            Users::class,
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

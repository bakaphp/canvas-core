<?php
declare(strict_types=1);

namespace Canvas\Models;

class PaymentFrequencies extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_frequencies');

        $this->hasMany(
            'id',
            'Canvas\Models\AppsPlans',
            'payment_frequencies_id',
            ['alias' => 'plans']
        );
    }
}

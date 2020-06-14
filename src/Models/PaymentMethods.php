<?php
declare(strict_types=1);

namespace Canvas\Models;

class PaymentMethods extends AbstractModel
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
    public $is_default;

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
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_methods');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public static function getDefault(): self
    {
        return self::findFirst(['conditions' => 'is_default = 1 and is_deleted = 0']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }
}

<?php
declare(strict_types=1);

namespace Canvas\Models;

class PaymentMethods extends AbstractModel
{
    public string $name;
    public int $is_default;

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
    public static function getDefault() : self
    {
        return self::findFirst(['conditions' => 'is_default = 1 and is_deleted = 0']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }
}

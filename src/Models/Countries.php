<?php
declare(strict_types=1);

namespace Canvas\Models;

class Countries extends AbstractModel
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
     * @var string
     */
    public $flag;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('countries');
    }

}

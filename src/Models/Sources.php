<?php

declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Exception\ModelException;


/**
 * Class Resources
 *
 * @package Canvas\Models
 *
 * @property \Phalcon\Di $di
 */
class Sources extends AbstractModel
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
    public $title;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var integer
     */
    public $language_id;

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
        $this->setSource('sources');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'sources';
    }

    /**
     * Validate is source is from apple
     *
     * @return bool
     */
    public function isApple(): bool
    {
        return $this->title == 'apple' ?: false;
    }
}

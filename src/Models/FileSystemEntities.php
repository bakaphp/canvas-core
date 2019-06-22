<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class FileSystemEntities extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $filesystem_id;

    /**
     *
     * @var integer
     */
    public $entity_id;

    /**
     *
     * @var integer
     */
    public $system_modules_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var string
     */
    public $field_name;

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
        $this->setSource('filesystem_entities');

        $this->belongsTo(
            'filesystem_id',
            'Canvas\Models\Filesystem',
            'id',
            ['alias' => 'file']
        );
    }

    /**
     * Validate the model.
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            ['filesystem_id', 'entity_id', 'system_modules_id'],
            new Uniqueness(
                [
                    'message' => 'Cant attach a file to a entity two times',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'filesystem_entities';
    }
}

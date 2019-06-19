<?php
declare(strict_types=1);

namespace Canvas\Models;

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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'filesystem_entities';
    }
}

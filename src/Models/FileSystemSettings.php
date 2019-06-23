<?php
declare(strict_types=1);

namespace Canvas\Models;

class FileSystemSettings extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $filesystem_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $value;

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
        $this->setSource('filesystem_settings');

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
        return 'filesystem_settings';
    }
}

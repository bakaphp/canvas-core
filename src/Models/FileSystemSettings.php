<?php
declare(strict_types=1);

namespace Canvas\Models;

class FileSystemSettings extends AbstractModel
{
    public int $filesystem_id;
    public string $name;
    public ?string $value = null;

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
}

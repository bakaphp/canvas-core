<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;

class NotificationChannels extends AbstractModel
{
    public string $name;
    public string $slug;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notification_channels');
    }

    /**
     * Get the notification by its slug.
     *
     * @param string $slug
     *
     * @return void
     */
    public static function getBySlug(string $slug) : self
    {
        return self::findFirstOrFail([
            'conditions' => 'slug = :slug: and is_deleted = 0',
            'bind' => [
                'slug' => $slug
            ]
        ]);
    }

    /**
     * Before create system modules forms.
     *
     * @return void
     */
    public function beforeCreate()
    {
        $this->slug = strtolower(trim($this->name));
    }
}

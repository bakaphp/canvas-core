<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\CustomFields\AppsCustomFields as BakaAppsCustomFields;
use Phalcon\Mvc\ModelInterface;

class AppsCustomFields extends BakaAppsCustomFields
{
    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('apps_custom_fields');
    }

    /**
     * For the given custom field , get its related entity.
     *
     * @return ModelInterface|null
     */
    public function getEntity() : ?ModelInterface
    {
        return $this->model_name::findFirst($this->entity_id);
    }
}

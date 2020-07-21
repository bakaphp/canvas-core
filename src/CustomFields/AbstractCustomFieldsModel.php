<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Canvas\Models\AbstractModel;

abstract class AbstractCustomFieldsModel extends AbstractModel
{
    use CustomFieldsTrait;
}

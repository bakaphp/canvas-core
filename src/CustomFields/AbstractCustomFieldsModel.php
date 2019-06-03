<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use Canvas\Models\CustomFieldsModules;
use Canvas\Models\AbstractModel;
use Baka\Database\Contracts\CustomFields\CustomFieldsTrait;
use PDO;

/**
 * Custom Fields Abstract Class.
 * @property \Phalcon\Di $di
 */
abstract class AbstractCustomFieldsModel extends AbstractModel
{
    use CustomFieldsTrait;

    /**
     * Get all custom fields of the given object.
     *
     * @param  array  $fields
     * @return array
     */
    public function getAllCustomFields(array $fields = [])
    {
        //We does it only find names in plural? We need to fix this or make a workaroun
        if (!$models = CustomFieldsModules::findFirstByName($this->getSource())) {
            return;
        }

        $bind = [$this->getId(), $this->di->getApp()->getId(), $models->getId(), $this->di->getUserData()->currentCompanyId()];

        // $customFieldsValueTable = $this->getSource() . '_custom_fields';
        $customFieldsValueTable = $this->getSource() . '_custom_fields';

        //We are to make a new query to replace old Canvas implementation.
        $result = $this->getReadConnection()->prepare("SELECT l.{$this->getSource()}_id,
                                               c.id as field_id,
                                               c.name,
                                               l.value ,
                                               c.users_id,
                                               l.created_at,
                                               l.updated_at
                                        FROM {$customFieldsValueTable} l,
                                             custom_fields c
                                        WHERE c.id = l.custom_fields_id
                                          AND l.{$this->getSource()}_id = ?
                                          AND c.apps_id = ?
                                          AND c.custom_fields_modules_id = ?
                                          AND c.companies_id = ? ");

        $result->execute($bind);

        $listOfCustomFields = [];

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $listOfCustomFields[$row->name] = $row->value;
        }

        return $listOfCustomFields;
    }

    /**
    * Before create.
    *
    * @return void
    */
    public function beforeCreate()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = null;
        $this->is_deleted = 0;
    }
}

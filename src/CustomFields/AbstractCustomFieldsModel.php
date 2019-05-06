<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use Canvas\Models\CustomFieldsModules;
use Exception;

/**
 * Custom Fields Abstract Class.
 * @property \Phalcon\Di $di
 */
abstract class AbstractCustomFieldsModel extends \Baka\Database\ModelCustomFields
{
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

        $bind = [$this->getId(), $this->di->getApp()->getId(), $models->getId(), $this->di->getUserData()->default_company];

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

        // $listOfCustomFields = $result->fetchAll();
        $listOfCustomFields = [];

        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $listOfCustomFields[$row->name] = $row->value;
        }

        return $listOfCustomFields;
    }

    /**
     * Get all custom fields of the given model.
     *
     * @param  array  $fields
     * @return \Phalcon\Mvc\Model
     */
    public function getCustomFieldsByModel($modelName)
    {
        if (!$module = CustomFieldsModules::findFirstByName($modelName)) {
            return;
        }
        $allFields = [];
        if ($fields = CustomFields::findByModulesId($module->id)->toArray()) {
            foreach ($fields as $field) {
                array_push($allFields, $field['name']);
            }
            return $allFields;
        }
    }

    /**
    * Create new custom fields.
    *
    * We never update any custom fields, we delete them and create them again, thats why we call cleanCustomFields before updates
    *
    * @return bool
    */
    protected function saveCustomFields(): bool
    {
        //find the custom field module
        if (!$module = CustomFieldsModules::findFirstByName($this->getSource())) {
            return false;
        }
        //we need a new instane to avoid overwrite
        $reflector = new \ReflectionClass($this);
        $classNameWithNameSpace = $reflector->getNamespaceName() . '\\' . $reflector->getShortName() . 'CustomFields';

        //if all is good now lets get the custom fields and save them
        foreach ($this->customFields as $key => $value) {
            //create a new obj per itration to se can save new info
            $customModel = new $classNameWithNameSpace();

            //validate the custome field by it model
            $customField = CustomFields::findFirst([
                'conditions' => 'name = ?0 AND custom_fields_modules_id = ?1 AND companies_id = ?2 AND apps_id = ?3',
                'bind' => [$key, $module->id, $this->di->getUserData()->default_company, $this->di->getApp()->getId()]
            ]);

            if ($customField) {
                $customModel->setCustomId($this->getId());
                $customModel->custom_fields_id = $customField->id;
                $customModel->value = $value;
                $customModel->created_at = date('Y-m-d H:i:s');

                if (!$customModel->save()) {
                    throw new Exception('Custome ' . $key . ' - ' . current($customModel->getMessages()));
                }
            }
        }

        //clean
        unset($this->customFields);

        return true;
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

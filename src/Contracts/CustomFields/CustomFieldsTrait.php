<?php

namespace Canvas\Contracts\CustomFields;

use Baka\Auth\UserProvider;
use Baka\Contracts\CustomFields\CustomFieldsTrait as CustomFieldsCustomFieldsTrait;
use Canvas\CustomFields\CustomFields;
use Canvas\Models\AppsCustomFields;
use Canvas\Models\CustomFieldsModules;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Utils\Slug;

/**
 * Custom field class.
 */
trait CustomFieldsTrait
{
    use CustomFieldsCustomFieldsTrait;

    public array $customFields = [];

    /**
     * Get the custom fields of the current object.
     *
     * @return array
     *
     */
    public static function getCustomFields(?ModelInterface $className = null) : array
    {
        $class = !is_null($className) ? get_class($className) : static::class;

        if (!$module = CustomFieldsModules::findFirstByModelName($class)) {
            return [];
        }

        $customFields = CustomFields::findByCustomFieldsModulesId($module->getId());

        foreach ($customFields as $customField) {
            $result[$customField->label ?? $customField->name] = [
                'type' => $customField->type->name,
                'label' => $customField->name,
                'attributes' => $customField->attributes ? json_decode($customField->attributes) : null
            ];
        }

        return $result;
    }

    /**
     * Get a Custom Field.
     *
     * @param string $name
     *
     * @return ModelInterface|null
     */
    public function getCustomField(string $name) : ?ModelInterface
    {
        return AppsCustomFields::findFirst([
            'conditions' => 'companies_id = :companies_id:  AND model_name = :model_name: AND entity_id = :entity_id: AND name = :name:',
            'bind' => [
                'companies_id' => $this->companies_id,
                'model_name' => get_class($this),
                'entity_id' => $this->getId(),
                'name' => $name,
            ]
        ]);
    }

    /**
     * Set value.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return ModelInterface
     */
    public function set(string $name, $value)
    {
        $companyId = $this->companies_id ?? 0;

        $this->setInRedis($name, $value);

        $this->createCustomField($name);

        return AppsCustomFields::updateOrCreate([
            'conditions' => 'companies_id = :companies_id:  AND model_name = :model_name: AND entity_id = :entity_id: AND name = :name:',
            'bind' => [
                'companies_id' => $companyId,
                'model_name' => get_class($this),
                'entity_id' => $this->getId(),
                'name' => $name,
            ]
        ], [
            'companies_id' => $companyId,
            'users_id' => UserProvider::get()->getId(),
            'model_name' => get_class($this),
            'entity_id' => $this->getId(),
            'label' => $name,
            'name' => $name,
            'value' => !is_array($value) ? $value : json_encode($value)
        ]);
    }

    /**
     * Create a new Custom Fields.
     *
     * @param string $name
     *
     * @return CustomFields
     */
    public function createCustomField(string $name) : CustomFields
    {
        $di = Di::getDefault();
        $appsId = $di->has('app') ? $di->get('app')->getId() : 0;
        $companiesId = $di->has('userData') ? UserProvider::get()->currentCompanyId() : 0;
        $textField = 1;
        $cacheKey = Slug::generate(get_class($this) . '-' . $appsId . '-' . $name);
        $lifetime = 604800;

        $customFieldModules = CustomFieldsModules::findFirstOrCreate(
            [
                'conditions' => 'model_name = :model_name: AND apps_id = :apps_id:',
                'bind' => [
                    'model_name' => get_class($this),
                    'apps_id' => $appsId
                ]],
            [
                'model_name' => get_class($this),
                'companies_id' => $companiesId,
                'name' => get_class($this),
                'apps_id' => $appsId
            ]
        );

        $customField = CustomFields::findFirstOrCreate([
            'conditions' => 'apps_id = :apps_id: AND name = :name: AND custom_fields_modules_id = :custom_fields_modules_id:',
            'bind' => [
                'apps_id' => $appsId,
                'name' => $name,
                'custom_fields_modules_id' => $customFieldModules->getId(),
            ]
        ], [
            'users_id' => $di->has('userData') ? UserProvider::get()->getId() : 0,
            'companies_id' => $companiesId,
            'apps_id' => $appsId,
            'name' => $name,
            'label' => $name,
            'custom_fields_modules_id' => $customFieldModules->getId(),
            'fields_type_id' => $textField,
        ]);

        return $customField;
    }
}

<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Users;
use Canvas\Exception\ServerErrorHttpException;
use Canvas\Exception\ModelException;

/**
 * Trait ResponseTrait.
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property Config $config
 * @property Request $request
 * @property Auth $auth
 * @property \Phalcon\Di $di
 *
 */
trait ModelSettingsTrait
{
    protected $settingsModel;

    /**
     * Set the setting model.
     *
     * @return void
     */
    private function createSettingsModel(): void
    {
        $class = get_class($this) . 'Settings';

        $this->settingsModel = new $class();
    }

    /**
     * Get the primary key of this model, this will only work on model with just 1 primary key.
     *
     * @return string
     */
    private function getPrimaryKey(): string
    {
        // Get the first matching primary key.
        // @TODO This will hurt on compound primary keys.
        $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
        // Get the first matching primary key.
        // @TODO This will hurt on compound primary keys.
        return $this->getSource() . '_' . $metaData->getPrimaryKeyAttributes($this)[0];
    }

    /**
     * Set the settings.
     *
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function setSettings(string $key, $value) : bool
    {
        $this->createSettingsModel();

        if (!is_object($this->settingsModel)) {
            throw new ServerErrorHttpException('ModelSettingsTrait need to have a settings model configure, check the model setting existe for this class' . get_class($this));
        }

        //if we dont find it we create it
        if (empty($this->settingsModel = $this->getSettingsByKey($key))) {
            /**
             * @todo this is stupid look for a better solution
             */
            $this->createSettingsModel();
            $this->settingsModel->{$this->getPrimaryKey()} = $this->getId();
        }

        $this->settingsModel->name = $key;
        $this->settingsModel->value = $value;
        if (!$this->settingsModel->save()) {
            throw new ModelException((string)current($this->settingsModel->getMessages()));
        }

        return true;
    }

    /**
     * Get the settings by its key.
     */
    protected function getSettingsByKey(string $key)
    {
        return $this->settingsModel->findFirst([
            'conditions' => "{$this->getPrimaryKey()} = ?0 and name = ?1",
            'bind' => [$this->getId(), $key]
        ]);
    }

    /**
     * Get the settings base on the key.
     *
     * @param string $key
     * @return void
     */
    public function getSettings(string $key): ?string
    {
        $this->createSettingsModel();
        $value = $this->getSettingsByKey($key);

        if (is_object($value)) {
            return $value->value;
        }

        return null;
    }

    /**
     * Get all the setting of a given record.
     *
     * @return array
     */
    public function getAllSettings(): array
    {
        $this->createSettingsModel();

        $allSettings = [];
        $settings = $this->settingsModel->find([
            'conditions' => "{$this->getPrimaryKey()} = ?0",
            'bind' => [$this->getId()]
        ]);

        foreach ($settings as $setting) {
            $allSettings[$setting->name] = $setting->value;
        }

        return $allSettings;
    }

    /**
     * Trim spaces from  properties's values of objects.
     * @todo Find a more elegant solution for trimming values
     * @return void
     */
    private function trimSpacesFromPropertiesValues(): void
    {
        foreach ($this as $key => $value) {
            if (gettype($value) == 'string') {
                $this->$key = trim($value);
            }
        }
    }
}

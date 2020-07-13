<?php

declare(strict_types=1);

namespace Canvas\Traits;

/**
 * Trait FractalTrait.
 *
 * @package Canvas\Traits
 */
trait KeyValueTablerait
{
    /**
     * Create a new setting for a model.
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function createSetting(string $key, string $value) : void
    {
        $classIdField = ucfirst(get_class($this)) . '_id';
        $setting = new self();
        $setting->$classIdField = $this->id;
        $setting->key = $key;
        $setting->value = $value;
        $setting->saveOrFail();
    }

    /**
     * Get setting of a model.
     *
     * @param $key
     *
     * @return void
     */
    public function getSetting(string $key) : void
    {
    }
}

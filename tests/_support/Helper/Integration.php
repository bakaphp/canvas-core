<?php

namespace Helper;

use Baka\Database\SystemModules;
use Codeception\Module;
use Codeception\Exception\TestRuntimeException;
use Codeception\TestInterface;
use Canvas\Bootstrap\Api;
use Canvas\Models\Users;
use Niden\Models\Companies;
use Niden\Models\CompaniesXProducts;
use Niden\Models\Individuals;
use Niden\Models\IndividualTypes;
use Niden\Models\Products;
use Niden\Models\ProductTypes;
use Niden\Mvc\Model\AbstractModel;
use Phalcon\DI\FactoryDefault as PhDI;
use Phalcon\Config as PhConfig;
use Canvas\Bootstrap\IntegrationTests;
use Canvas\Models\FileSystem;
use Canvas\Models\FileSystemEntities;
use function Baka\appPath;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Integration extends Module
{
    /**
     * @var null|PhDI
     */
    protected $diContainer = null;
    protected $savedModels = [];
    protected $savedRecords = [];
    protected $config = ['rollback' => false];

    /**
     * Test initializer.
     */
    public function _before(TestInterface $test)
    {
        PhDI::reset();
        $app = new IntegrationTests();
        $app->setup();
        $this->diContainer = $app->getContainer();

        if ($this->config['rollback']) {
            $this->diContainer->get('db')->begin();
        }

        //Set default user
        $user = Users::findFirst(1);
        $this->diContainer->setShared('userData', $user);
        $this->savedModels = [];
        $this->savedRecords = [];
    }

    public function _after(TestInterface $test)
    {
        if (!$this->config['rollback']) {
            foreach ($this->savedRecords as $record) {
                $record->delete();
            }
        } else {
            $this->diContainer->get('db')->rollback();
        }
        $this->diContainer->get('db')->close();
    }

    /**
     * @return mixed
     */
    public function grabDi()
    {
        return $this->diContainer;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function grabFromDi(string $name)
    {
        return $this->diContainer->get($name);
    }

    /**
     * Returns the relationships that a model has.
     *
     * @param string $class
     *
     * @return array
     */
    public function getModelRelationships(string $class): array
    {
        /** @var AbstractModel $class */
        $model = new $class();
        $manager = $model->getModelsManager();
        $relationships = $manager->getRelations($class);
        $data = [];
        foreach ($relationships as $relationship) {
            $data[] = [
                $relationship->getType(),
                $relationship->getFields(),
                $relationship->getReferencedModel(),
                $relationship->getReferencedFields(),
                $relationship->getOptions(),
            ];
        }
        return $data;
    }

    /**
     * Get a record from $modelName with fields provided.
     *
     * @param string $modelName
     * @param array  $fields
     *
     * @return bool|AbstractModel
     */
    public function getRecordWithFields(string $modelName, $fields = [])
    {
        $record = false;
        if (count($fields) > 0) {
            $conditions = '';
            $bind = [];
            foreach ($fields as $field => $value) {
                $conditions .= sprintf(
                    '%s = :%s: AND ',
                    $field,
                    $field
                );
                $bind[$field] = $value;
            }
            $conditions = rtrim($conditions, ' AND ');
            /** @var AbstractModel $record */
            $record = $modelName::findFirst(
                [
                    'conditions' => $conditions,
                    'bind' => $bind,
                ]
            );
        }
        return $record;
    }

    /**
     * @param array $configData
     */
    public function haveConfig(array $configData)
    {
        $config = new PhConfig($configData);
        $this->diContainer->set('config', $config);
    }

    /**
     * Checks model fields.
     *
     * @param string $modelName
     * @param array  $fields
     */
    public function haveModelDefinition(string $modelName, array $fields)
    {
        /** @var AbstractModel $model */
        $model = new $modelName;
        $metadata = $model->getModelsMetaData();
        $attributes = $metadata->getAttributes($model);
        $this->assertEquals(
            count($fields),
            count($attributes),
            "Field count not correct for $modelName"
        );
        foreach ($fields as $value) {
            $this->assertContains(
                $value,
                $attributes,
                "Field not exists in $modelName"
            );
        }
    }

    /**
     * Create a record for $modelName with fields provided.
     *
     * @param string $modelName
     * @param array  $fields
     *
     * @return mixed
     */
    public function haveRecordWithFields(string $modelName, array $fields = [])
    {
        $record = new $modelName;
        foreach ($fields as $key => $val) {
            $record->set($key, $val);
        }
        $this->savedModels[$modelName] = $fields;
        $result = $record->save();
        $this->assertNotSame(false, $result);
        $this->savedRecords[] = $record;
        return $record;
    }

    /**
     * @param string $name
     * @param mixed  $service
     */
    public function haveService(string $name, $service)
    {
        $this->diContainer->set($name, $service);
    }

    /**
     * @param string $name
     */
    public function removeService(string $name)
    {
        if ($this->diContainer->has($name)) {
            $this->diContainer->remove($name);
        }
    }

    /**
     * Check that record created with haveRecordWithFields can be fetched and
     * all its fields contain valid values.
     *
     * @param       $modelName
     * @param       $by
     * @param array $except
     *
     * @return mixed
     */
    public function seeRecordFieldsValid($modelName, $by, array $except = [])
    {
        if (!isset($this->savedModels[$modelName])) {
            throw new TestRuntimeException(
                'Should be used after haveModelWithFields with ' . $modelName
            );
        }
        $fields = $this->savedModels[$modelName];
        if (!is_array($by)) {
            $by = [$by];
        }
        $bySelector = implode(
            ' AND ',
            array_map(
                function ($key) {
                    return "$key = :$key:";
                },
                $by
            )
        );
        $byBind = [];
        foreach ($by as $byVal) {
            if (!isset($fields[$byVal])) {
                throw new TestRuntimeException("Field $byVal is not set");
            }
            $byBind[$byVal] = $fields[$byVal];
        }
        $record = call_user_func(
            [
                $modelName, 'findFirst',
            ],
            [
                'conditions' => $bySelector,
                'bind' => $byBind,
            ]
        );
        if (!$record) {
            $this->fail("Record $modelName for $by not found");
        }
        foreach ($fields as $key => $val) {
            if (isset($except[$key])) {
                continue;
            }
            $this->assertEquals(
                $val,
                $record->get($key),
                "Field in $modelName for $key not valid"
            );
        }
        return $record;
    }

    /**
     * Checks that record exists and has provided fields.
     *
     * @param $model
     * @param $by
     * @param $fields
     */
    public function seeRecordSaved($model, $by, $fields)
    {
        $this->savedModels[$model] = array_merge($by, $fields);
        $record = $this->seeRecordFieldsValid(
            $model,
            array_keys($by),
            array_keys($by)
        );
        $this->savedRecords[] = $record;
    }

    /**
     * Get a filesystem entity record from the current user.
     *
     * @return FileSystemEntities
     */
    public function getFileSystemEntity(): FileSystemEntities
    {
        $newFilesystem = new FileSystem();
        $newFilesystem->companies_id = $this->grabFromDi('userData')->currentCompanyId();
        $newFilesystem->apps_id = $this->grabFromDi('app')->getId();
        $newFilesystem->users_id = $this->grabFromDi('userData')->getId();
        $newFilesystem->name = 'logo.png';
        $newFilesystem->path = '/logo/logo.png';
        $newFilesystem->url = 'http://kanvas.dev/logo.png';
        $newFilesystem->size = '10';
        $newFilesystem->file_type = 'jpg';
        $newFilesystem->saveOrFail();

        $systemModule = SystemModules::findFirst(1);

        $fileSystemEntities = new FileSystemEntities();
        $fileSystemEntities->filesystem_id = $newFilesystem->getId();
        $fileSystemEntities->entity_id = $this->grabFromDi('userData')->getDefaultCompany()->getId();
        $fileSystemEntities->companies_id = $this->grabFromDi('userData')->getDefaultCompany()->getId();
        $fileSystemEntities->system_modules_id = $systemModule->getId();
        $fileSystemEntities->field_name = 'logo';
        $fileSystemEntities->saveOrFail();

        return $fileSystemEntities;
    }
}

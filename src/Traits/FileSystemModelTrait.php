<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Database\Model;
use Canvas\Models\SystemModules;
use Canvas\Models\FileSystem;
use Canvas\Exception\ModelException;
use Canvas\Models\FileSystemSettings;
use Phalcon\Mvc\Model\Resultset\Simple;
use Canvas\Models\FileSystemEntities;
use Canvas\Dto\Files;
use Canvas\Mapper\FileMapper;

/**
 * Trait ResponseTrait.
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property AppsPlans $appPlan
 * @property CompanyBranches $branches
 * @property Companies $company
 * @property UserCompanyApps $app
 * @property \Phalcon\Di $di
 *
 */
trait FileSystemModelTrait
{
    public $uploadedFiles = [];

    /**
     * Associated the list of uploaded files to this entity.
     *
     * call on the after saves
     *
     * @return void
     */
    protected function associateFileSystem(): bool
    {
        if (!empty($this->uploadedFiles) && is_array($this->uploadedFiles)) {
            //look for the current system module
            $systemModule = SystemModules::getSystemModuleByModelName(self::class);

            foreach ($this->uploadedFiles as $file) {
                if (!isset($file['id'])) {
                    continue;
                }

                if ($file = FileSystem::getById($file['id'])) {
                    $fileSystemEntities = new FileSystemEntities();
                    $fileSystemEntities->filesystem_id = $file->getId();
                    $fileSystemEntities->system_modules_id = $systemModule->getId();
                    $fileSystemEntities->field_name = $file['field_name'] ?? ''; //field_name for this specify relationship
                    $fileSystemEntities->entity_id = $this->getId();

                    $fileSystemEntities->saveOrFail();
                }
            }
        }

        return true;
    }

    /**
     * Over write, because of the phalcon events.
     *
     * @param array data
     * @param array whiteList
     * @return boolean
     */
    public function update($data = null, $whiteList = null): bool
    {
        //associate uploaded files
        if (isset($data['files'])) {
            $this->uploadedFiles = $data['files'];
        }

        return parent::update($data, $whiteList);
    }

    /**
     * Inserts or updates a model instance. Returning true on success or false otherwise.
     *
     *<code>
     * // Creating a new robot
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * $robot->save();
     *
     * // Updating a robot name
     * $robot = Robots::findFirst("id = 100");
     *
     * $robot->name = "Biomass";
     *
     * $robot->save();
     *</code>
     *
     * @param array data
     * @param array whiteList
     * @return boolean
     */
    public function save($data = null, $whiteList = null): bool
    {
        //associate uploaded files
        if (isset($data['files'])) {
            $this->uploadedFiles = $data['files'];
        }

        return parent::save($data, $whiteList);
    }

    /**
     * Delete all the files from a module.
     *
     * @return void
     */
    public function deleteFiles(): bool
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $files = FileSystem::getAllByEntityId($this->getId(), $systemModule);
        $files->delete();

        return true;
    }

    /**
     * Given the array of files we will attch this files to the files.
     * [
     *  'file' => $file,
     *  'file_name' => 'dfadfa'
     * ];.
     *
     * @param array $files
     * @return void
     */
    public function attach(array $files): bool
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        foreach ($files as $file) {

            //im looking for the file inside an array
            if (!array_key_exists('file', $file)) {
                continue;
            }

            //attach to the entity
            $fileSystemEntities = new FileSystemEntities();
            $fileSystemEntities->filesystem_id = $file['file']->getId();
            $fileSystemEntities->entity_id = $this->getId();
            $fileSystemEntities->system_modules_id = $systemModule->getId();
            $fileSystemEntities->field_name = $file['field_name'] ?? null;
            $fileSystemEntities->saveOrFail();
        }

        return true;
    }

    /**
     * Get all the files attach for the given module.
     *
     * @return Simple
     */
    public function getAttachments(string $fileType = null) : array
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $bindParams = [
            0,
            $systemModule->getId(),
            $this->getId()
        ];

        /**
         * We can also filter the attachements by its file type.
         */
        $fileTypeSql = !is_null($fileType) ? 'AND file_type = ?3' : null;
        if ($fileTypeSql) {
            $bindParams[] = $fileType;
        }

        $attachments = FileSystem::find([
            'conditions' => '
                is_deleted = ?0 AND id in 
                    (SELECT 
                        filesystem_id from \Canvas\Models\FileSystemEntities e
                        WHERE e.system_modules_id = ?1 AND e.entity_id = ?2
                    )' . $fileTypeSql,
            'bind' => $bindParams
        ]);

        $fileMapper = new FileMapper();
        $fileMapper->systemModuleId = $systemModule->getId();
        $fileMapper->entityId = $this->getId();

        //add a mapper
        $this->di->getDtoConfig()->registerMapping(FileSystem::class, Files::class)
            ->useCustomMapper($fileMapper);

        return $this->di->getMapper()->mapMultiple($attachments, Files::class);
    }

    /**
     * Given the file id get all attributes.
     *
     * @param integer $fileId
     * @return array
     */
    public function getFileAllAttributes(int $fileId): array
    {
        $attributes = [];
        $settings = FileSystemSettings::find([
            'conditions' => 'filesystem_id = ?0',
            'bind' => [$fileId]
        ]);

        foreach ($settings as $setting) {
            $attributes[$setting->name] = $setting->value;
        }

        return $attributes;
    }
}

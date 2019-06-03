<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Database\Model;
use Canvas\Models\SystemModules;
use Canvas\Models\FileSystem;
use Canvas\Exception\ModelException;
use Canvas\Models\FileSystemSettings;
use Phalcon\Mvc\Model\Resultset\Simple;

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
            //find the current asociated fil
            if ($allCurrentFiles = FileSystem::getAllByEntityId($this->getId(), $systemModule)) {
                foreach ($allCurrentFiles as $currentFile) {
                    //release it, since we dont knee dyou here any more
                    $currentFile->entity_id = 0;
                    //$currentFile->update();
                    //but lets keep a record or you pass location
                    $currentFile->set('old_entity_id', $this->getId());
                }
            }

            foreach ($this->uploadedFiles as $file) {
                if (!isset($file['id'])) {
                    continue;
                }

                if ($file = FileSystem::getById($file['id'])) {
                    $file->system_modules_id = $systemModule->getId();
                    $file->entity_id = $this->getId();
                    $file->set('entity_id', $this->getId());

                    if (!$file->update()) {
                        throw new ModelException(current($this->getMessages())->getMessage());
                    }
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
     * Get all the files attach for the given module.
     *
     * @return Simple
     */
    public function getAttachments() : Simple
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $attachments = FileSystem::find([
            'conditions' => 'is_deleted = 0
                AND system_modules_id = :moduleId:
                AND entity_id = :entityId:',
            'bind' => [
                'entityId' => $this->getId(),
                'moduleId' => $systemModule->getId()
            ]
        ]);

        return $attachments;
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

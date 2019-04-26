<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Database\Model;
use Canvas\Models\SystemModules;
use Canvas\Models\FileSystem;
use Canvas\Exception\ModelException;

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
        foreach ($this->uploadedFiles as $file) {
            if (!isset($file['id'])) {
                continue;
            }
            if ($file = FileSystem::getById($file['id'])) {
                $file->entity_id = $this->getId();

                if (!$file->update()) {
                    throw new ModelException(current($this->getMessages())->getMessage());
                }
            }
        }

        return true;
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
        if (isset($data['uploadedFiles'])) {
            $this->uploadedFiles = $data['uploadedFiles'];
        }

        return parent::save($data, $whiteList);
    }

    /**
     * Delete all the files from a module.
     *
     * @return void
     */
    public function deleteAllFiles(): bool
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $files = FileSystem::getAllByEntityId($this->getId(), $systemModule);
        $files->delete();

        return true;
    }
}

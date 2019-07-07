<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\SystemModules;
use Canvas\Models\FileSystem;
use RuntimeException;
use Phalcon\Mvc\Model\Resultset\Simple;
use Canvas\Models\FileSystemEntities;
use Canvas\Dto\Files;
use Canvas\Mapper\FileMapper;
use Phalcon\Di;

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
            foreach ($this->uploadedFiles as $file) {
                if (!isset($file['filesystem_id'])) {
                    continue;
                }

                if ($fileSystem = FileSystem::getById($file['filesystem_id'])) {
                    $this->attach([[
                        'id' => $file['id'] ?: 0,
                        'file' => $fileSystem,
                        'field_name' => $file['field_name'] ?? ''
                    ]]);
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
            if (!empty($data['files'])) {
                $this->uploadedFiles = $data['files'];
            } else {
                $this->deleteFiles();
            }
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
            if (!empty($data['files'])) {
                $this->uploadedFiles = $data['files'];
            } else {
                $this->deleteFiles();
            }
        }

        return parent::save($data, $whiteList);
    }

    /**
     * Delete all the files from a module.
     *
     * @return bool
     */
    public function deleteFiles(): bool
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        if ($files = FileSystemEntities::getAllByEntityId($this->getId(), $systemModule)) {
            foreach ($files as $file) {
                $file->softDelete();
            }
        }

        return true;
    }

    /**
     * Given the ID delete the file from this entity.
     *
     * @param integer $id
     * @return bool
     */
    public function deleteFile(int $id)
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $file = FileSystemEntities::findFirstOrFail([
            'contidions' => 'id = ?0 AND entity_id = ?1 AND system_modules_id = ?2 AND is_deleted = ?3',
            'bind' => [$id, $this->getId(), $systemModule->getId(), 0]
        ]);

        return $file->softDelete();
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

            if (!$file['file'] instanceof FileSystem) {
                throw new RuntimeException('Cant attach a one Filesytem to this entity');
            }

            //check if we are updating the attachment
            if (array_key_exists('id', $file) && (int) $file['id']) {
                $fileSystemEntities = FileSystemEntities::getByIdWithSystemModule($file['id'], $systemModule);
            }

            //new attachment
            if (!is_object($fileSystemEntities)) {
                $fileSystemEntities = new FileSystemEntities();
                $fileSystemEntities->filesystem_id = $file['file']->getId();
                $fileSystemEntities->system_modules_id = $systemModule->getId();
                $fileSystemEntities->companies_id = $file['file']->companies_id;
                $fileSystemEntities->entity_id = $this->getId();
                $fileSystemEntities->created_at = $file['file']->created_at;
            }

            $fileSystemEntities->field_name = $file['field_name'] ?? null;
            $fileSystemEntities->is_deleted = 0 ;
            $fileSystemEntities->saveOrFail();
        }

        return true;
    }

    /**
     * Get all the files attach for the given module.
     *
     * @param string $fileType filter the files by their type
     * @return array
     */
    public function getAttachments(string $fileType = null) : array
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $bindParams = [
            0,
            Di::getDefault()->getUserData()->currentCompanyId(),
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
                is_deleted = ?0 AND companies_id = ?1 AND id in 
                    (SELECT 
                        filesystem_id from \Canvas\Models\FileSystemEntities e
                        WHERE e.system_modules_id = ?2 AND e.entity_id = ?3 AND e.is_deleted = ?0 and e.companies_id = ?1
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
     * Overwrite the relationship of the filesystem to return the attachment structure
     * to the given user.
     *
     * @deprecated version 0.2
     * @return array
     */
    public function getFilesystem(): array
    {
        return $this->getAttachments();
    }

    /**
     * Overwrite the relationship of the filesystem to return the attachment structure
     * to the given user.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->getAttachments();
    }

    /**
     * Get a file by its fieldname.
     *
     * @todo this will be a performance issue in the futur look for better ways to handle this
     * when a company has over 1k images
     *
     * @deprecated version 0.2
     * @param string $name
     * @return void
     */
    public function getAttachementByName(string $fieldName): ?string
    {
        return $this->getFileByName($fieldName);
    }

    /**
     * Undocumented function.
     *
     * @param string $fieldName
     * @return string|null
     */
    public function getFileByName(string $fieldName): ?string
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $companyId = Di::getDefault()->getUserData()->currentCompanyId();

        $fileEntity = FileSystemEntities::findFirst([
            'conditions' => 'system_modules_id = ?0 AND entity_id = ?1 AND is_deleted = ?2 and field_name = ?3 and companies_id = ?4
                            AND filesystem_id IN (SELECT f.id from \Canvas\Models\FileSystem f WHERE
                                f.is_deleted = ?2 AND f.companies_id = ?4
                            )',
            'bind' => [$systemModule->getId(), $this->getId(), 0, $fieldName, $companyId]
        ]);

        if ($fileEntity) {
            return $fileEntity->file->url;
        }

        return null;
    }
}

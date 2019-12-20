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
use Phalcon\Mvc\Model\ResultsetInterface;

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
                /**
                 * @todo for now lets delete them all and updated them
                 * look for a better solution later, this can since we are not using transactions
                 */
                $this->deleteFiles();

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
            $files->update([], function ($file) {
                $file->softDelete();
            });
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

        if ($file) {
            $file->softDelete();
        }

        return false;
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
            if (!isset($file['file'])) {
                continue;
            }

            if (!$file['file'] instanceof FileSystem) {
                throw new RuntimeException('Cant attach a none Filesytem to this entity');
            }

            $fileSystemEntities = null;
            //check if we are updating the attachment
            if ($id = (int) $file['id']) {
                $fileSystemEntities = FileSystemEntities::getByIdWithSystemModule($id, $systemModule);
            }

            //new attachment
            if (!is_object($fileSystemEntities)) {
                $fileSystemEntities = new FileSystemEntities();
                $fileSystemEntities->system_modules_id = $systemModule->getId();
                $fileSystemEntities->companies_id = $file['file']->companies_id;
                $fileSystemEntities->entity_id = $this->getId();
                $fileSystemEntities->created_at = $file['file']->created_at;
            }

            $fileSystemEntities->filesystem_id = $file['file']->getId();
            $fileSystemEntities->field_name = $file['field_name'] ?? null;
            $fileSystemEntities->is_deleted = 0;
            $fileSystemEntities->saveOrFail();

            if (!is_null($this->filesNewAttachedPath())) {
                $file['file']->move($this->filesNewAttachedPath());
            }
        }

        return true;
    }

    /**
     * Get all the files attach for the given module.
     *
     * @param string $fileType filter the files by their type
     * @return array
     */
    public function getAttachments(string $fileType = null) : ResultsetInterface
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $companyId = $this->di->getUserData()->currentCompanyId();

        $bindParams = [
            $systemModule->getId(),
            $this->getId(),
            0,
            $companyId
        ];

        /**
         * We can also filter the attachements by its file type.
         */
        $fileTypeSql = !is_null($fileType) ? 'AND f.file_type = ?4' : null;
        if ($fileTypeSql) {
            $bindParams[] = $fileType;
        }

        return FileSystemEntities::find([
            'conditions' => 'system_modules_id = ?0 AND entity_id = ?1 AND is_deleted = ?2 and companies_id = ?3
                            AND filesystem_id IN (SELECT f.id from \Canvas\Models\FileSystem f WHERE
                                f.is_deleted = ?2 AND f.companies_id = ?3 ' . $fileTypeSql . '
                            )',
            'bind' => $bindParams
        ]);
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
        return $this->getFiles();
    }

    /**
     * Overwrite the relationship of the filesystem to return the attachment structure
     * to the given user.
     *
     * @return array
     */
    public function getFiles(string $fileType = null): array
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $attachments = $this->getAttachments($fileType);

        $fileMapper = new FileMapper($this->getId(), $systemModule->getId());

        //add a mapper
        $this->di->getDtoConfig()
            ->registerMapping(FileSystemEntities::class, Files::class)
            ->useCustomMapper($fileMapper);

        return $this->di->getMapper()->mapMultiple($attachments, Files::class);
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
    public function getAttachmentByName(string $fieldName)
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $companyId = $this->di->getUserData()->currentCompanyId();

        return FileSystemEntities::findFirst([
            'conditions' => 'system_modules_id = ?0 AND entity_id = ?1 AND is_deleted = ?2 and field_name = ?3 and companies_id = ?4
                            AND filesystem_id IN (SELECT f.id from \Canvas\Models\FileSystem f WHERE
                                f.is_deleted = ?2 AND f.companies_id = ?4
                            )',
            'bind' => [$systemModule->getId(), $this->getId(), 0, $fieldName, $companyId]
        ]);
    }

    /**
     * Undocumented function.
     *
     * @param string $fieldName
     * @return string|null
     */
    public function getFileByName(string $fieldName): ?object
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $fileEntity = $this->getAttachmentByName($fieldName);

        if ($fileEntity) {
            $fileMapper = new FileMapper($this->getId(), $systemModule->getId());

            //add a mapper
            $this->di->getDtoConfig()
                ->registerMapping(FileSystemEntities::class, Files::class)
                ->useCustomMapper($fileMapper);

            /**
             * @todo create a mapper for entity so we dont have to look for the relationship?
             */
            return $this->di->getMapper()->map($fileEntity, Files::class);
        }

        return null;
    }

    /**
     * Given this entity define a new path.
     *
     * @param string $path
     * @return string
     */
    protected function filesNewAttachedPath(): ?string
    {
        return null;
    }
}

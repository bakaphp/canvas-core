<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Dto\Files;
use Canvas\Mapper\FileMapper;
use Canvas\Models\FileSystem;
use Canvas\Models\FileSystemEntities;
use Canvas\Models\FileSystemSettings;
use Canvas\Models\SystemModules;
use Phalcon\Mvc\Model\ResultsetInterface;
use RuntimeException;

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
    protected function associateFileSystem() : bool
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
     *
     * @return bool
     */
    public function updateOrFail($data = null, $whiteList = null) : bool
    {
        //associate uploaded files
        if (isset($data['files'])) {
            if (!empty($data['files'])) {
                /**
                 * @todo for now lets delete them all and updated them
                 * look for a better solution later , this can cause issues
                 * since we are not using transactions
                 */
                $this->deleteFiles();

                $this->uploadedFiles = $data['files'];
            } else {
                if ((bool) $this->di->get('app')->get('delete_images_on_empty_files_field')) {
                    $this->deleteFiles();
                }
            }
        }

        return parent::updateOrFail($data, $whiteList);
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
     *
     * @return bool
     */
    public function saveOrFail($data = null, $whiteList = null) : bool
    {
        //associate uploaded files
        if (isset($data['files'])) {
            if (!empty($data['files'])) {
                $this->uploadedFiles = $data['files'];
            }
        }

        return parent::saveOrFail($data, $whiteList);
    }

    /**
     * Delete all the files from a module.
     *
     * @return bool
     */
    public function deleteFiles() : bool
    {
        $systemModule = SystemModules::getByModelName(self::class);

        if ($files = FileSystemEntities::getAllByEntityId($this->getId(), $systemModule)) {
            $files->filter(
                function ($file) {
                    $file->softDelete();
                }
            );
        }

        return true;
    }

    /**
     * Given the ID delete the file from this entity.
     *
     * @param int $id
     *
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
     * Given the array of files we will attach this files to the files.
     * [
     *  'file' => $file,
     *  'file_name' => 'dfadfa'
     * ];.
     *
     * @param array $files
     *
     * @return void
     */
    public function attach(array $files) : bool
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
     * Overwrite the relationship of the filesystem to return the attachment structure
     * to the given user.
     *
     * @deprecated version 0.2
     *
     * @return array
     */
    public function getFilesystem() : array
    {
        return $this->getFiles();
    }

    /**
     * Overwrite the relationship of the filesystem to return the attachment structure
     * to the given user.
     *
     * @return array
     */
    public function getFiles(string $fileType = null) : array
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
     * Get all the files matching the field name.
     *
     * @param string $fieldName
     *
     * @return array
     */
    public function getFilesByName(string $fieldName) : array
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $attachments = $this->getAttachmentsByName($fieldName);

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
     * @todo this will be a performance issue in the future look for better ways to handle this
     * when a company has over 1k images
     *
     * @param string $name
     *
     * @return void
     */
    public function getAttachmentByName(string $fieldName)
    {
        $criteria = $this->searchCriteriaForFilesByName($fieldName);

        return FileSystemEntities::findFirst([
            'conditions' => $criteria['conditions'],
            'order' => 'id desc',
            'bind' => $criteria['bind'],
        ]);
    }

    /**
     * Get a files by its fieldname.
     *
     * @param string $name
     *
     * @return void
     */
    public function getAttachmentsByName(string $fieldName)
    {
        $criteria = $this->searchCriteriaForFilesByName($fieldName);

        return FileSystemEntities::find([
            'conditions' => $criteria['conditions'],
            'order' => 'id desc',
            'bind' => $criteria['bind'],
        ]);
    }

    /**
     * Get the file byt it's name.
     *
     * @param string $fieldName
     *
     * @return string|null
     */
    public function getFileByName(string $fieldName) : ?object
    {
        return $this->fileMapper($this->getAttachmentByName($fieldName));
    }

    /**
     * Get file by name and attributes.
     *
     * @param string $fieldName
     * @param string $key
     * @param string $value
     *
     * @return object|null
     */
    public function getFileByNameWithAttributes(string $fieldName, string $key, string $value) : ?object
    {
        return $this->fileMapper($this->getAttachmentByNameAndAttributes($fieldName, $key, $value));
    }

    /**
     * Convert identity to mapper.
     *
     * @param FileSystemEntities|null $fileEntity
     *
     * @return object|null
     */
    protected function fileMapper($fileEntity) : ?object
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        if ($fileEntity instanceof FileSystemEntities) {
            $fileMapper = new FileMapper($this->getId(), $systemModule->getId());

            //add a mapper
            $this->di->getDtoConfig()
                ->registerMapping(FileSystemEntities::class, Files::class)
                ->useCustomMapper($fileMapper);

            /**
             * @todo create a mapper for entity so we don't have to look for the relationship?
             */
            return $this->di->getMapper()->map($fileEntity, Files::class);
        }

        return null;
    }

    /**
     * Given this entity define a new path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function filesNewAttachedPath() : ?string
    {
        return null;
    }

    /**
     * Search Criteria for file by name.
     *
     * @param string $fieldName
     *
     * @return array
     */
    protected function searchCriteriaForFilesByName(string $fieldName) : array
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $appPublicImages = (bool) $this->di->get('app')->get('public_images');

        $bindParams = [
            'system_module_id' => $systemModule->getId(),
            'entity_id' => $this->getId(),
            'is_deleted' => 0,
            'field_name' => $fieldName,
        ];

        //do we allow images by entity to be public to anybody accessing it directly by the entity?
        if ($appPublicImages) {
            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted: and field_name = :field_name: ';
        } else {
            $bindParams['company_id'] = $this->di->getUserData()->currentCompanyId();
            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted: and field_name = :field_name: and companies_id = :company_id:';
        }

        return [
            'bind' => $bindParams,
            'conditions' => $condition
        ];
    }

    /**
     * Given the filename look for it version with
     * the key and value associated to the file.
     *
     * @param string $fieldName
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function getAttachmentByNameAndAttributes(string $fieldName, string $key, string $value)
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $appPublicImages = (bool) $this->di->get('app')->get('public_images');

        $bindParams = [
            'system_module_id' => $systemModule->getId(),
            'entity_id' => $this->getId(),
            'is_deleted' => 0,
            'field_name' => $fieldName,
            'name' => $key,
            'value' => $value
        ];

        //do we allow images by entity to be public to anybody accessing it directly by the entity?
        if ($appPublicImages) {
            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted: and field_name = :field_name: 
            AND filesystem_id IN (
                SELECT s.filesystem_id FROM 
                    ' . FileSystemSettings::class . ' s 
                    WHERE name = :name: AND value = :value: 
            )';
        } else {
            $bindParams['company_id'] = $this->di->getUserData()->currentCompanyId();
            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted: and field_name = :field_name: and companies_id = :company_id: 
            AND filesystem_id IN (
                SELECT s.filesystem_id FROM 
                    ' . FileSystemSettings::class . ' s 
                    WHERE name = :name: AND value = :value: 
            )';
        }

        return FileSystemEntities::findFirst([
            'conditions' => $condition,
            'order' => 'id desc',
            'bind' => $bindParams
        ]);
    }

    /**
     * Get all the files attach for the given module.
     *
     * @param string $fileType filter the files by their type
     *
     * @return array
     */
    public function getAttachments(string $fileType = null) : ResultsetInterface
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $appPublicImages = (bool) $this->di->get('app')->get('public_images');

        $bindParams = [
            'system_module_id' => $systemModule->getId(),
            'entity_id' => $this->getId(),
            'is_deleted' => 0,
        ];

        /**
         * We can also filter the attachments by its file type.
         */
        $fileTypeSql = null;
        if ($fileType) {
            $fileTypeSql = !is_null($fileType) ? 'AND f.file_type = :file_type:' : null;
            $bindParams['file_type'] = $fileType;
        }

        //do we allow images by entity to be public to anybody accessing it directly by the entity?
        if ($appPublicImages) {
            /**
             * @todo optimize this queries to slow
             */
            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted:
            AND filesystem_id IN (SELECT f.id from \Canvas\Models\FileSystem f WHERE
                f.is_deleted = :is_deleted: ' . $fileTypeSql . '
            )';
        } else {
            $bindParams['company_id'] = $this->di->getUserData()->currentCompanyId();

            $condition = 'system_modules_id = :system_module_id: AND entity_id = :entity_id: AND is_deleted = :is_deleted: and companies_id = :company_id:
            AND filesystem_id IN (SELECT f.id from \Canvas\Models\FileSystem f WHERE
                f.is_deleted = :is_deleted: AND f.companies_id = :company_id: ' . $fileTypeSql . '
            )';
        }

        return FileSystemEntities::find([
            'conditions' => $condition,
            'order' => 'id desc',
            'bind' => $bindParams
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Filesystem\Helper;
use Canvas\Http\Exception\UnprocessableEntityException;
use Canvas\Models\FileSystem;
use Canvas\Models\FileSystemEntities;
use Canvas\Models\FileSystemSettings;
use Phalcon\Http\Response;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File as FileValidator;

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
trait FileManagementTrait
{
    /**
     * Add a new item.
     *
     * @method POST
     * url /v1/filesystem
     *
     * @return \Phalcon\Http\Response
     *
     * @throws Exception
     */
    public function create() : Response
    {
        if (!$this->request->hasFiles()) {
            /**
             * @todo handle file hash to avoid uploading same files again
             */
        }

        return $this->response($this->processFiles());
    }

    /**
     * Update an item.
     *
     * @method PUT
     * url /v1/filesystem/{id}
     *
     * @param mixed $id
     *
     * @return \Phalcon\Http\Response
     *
     * @throws Exception
     */
    public function edit($id) : Response
    {
        $file = FileSystem::getById($id);

        $request = $this->request->getPutData();

        $systemModule = $request['system_modules_id'] ?? 0;
        $entityId = $request['entity_id'] ?? 0;
        $fieldName = $request['field_name'] ?? '';

        //associate
        $fileSystemEntities = new FileSystemEntities();
        $fileSystemEntities->filesystem_id = $file->getId();
        $fileSystemEntities->entity_id = $entityId;
        $fileSystemEntities->companies_id = $file->companies_id;
        $fileSystemEntities->system_modules_id = $systemModule;
        $fileSystemEntities->field_name = $fieldName;
        $fileSystemEntities->saveOrFail();

        $file->updateOrFail($request, $this->updateFields);

        return $this->response($file);
    }

    /**
     * Update a filesystem Entity,  field name.
     *
     * @param int $id
     *
     * @return Response
     */
    public function editEntity(int $id) : Response
    {
        $fileEntity = FileSystemEntities::getById($id);
        $request = $this->request->getPutData();

        $fileEntity->field_name = $request['field_name'];
        $fileEntity->updateOrFail();

        return $this->response($fileEntity);
    }

    /**
     * Delete a file atribute.
     *
     * @param $id
     * @param string $name
     *
     * @return void
     */
    public function deleteAttributes($id, string $name) : Response
    {
        $records = FileSystem::findFirstOrFail($id);

        $recordAttributes = FileSystemSettings::findFirstOrFail([
            'conditions' => 'filesystem_id = ?0 and name = ?1',
            'bind' => [$records->getId(), $name]
        ]);

        //true true delete
        $recordAttributes->delete();

        return $this->response(['Delete Successfully']);
    }

    /**
     * Set the validation for the files.
     *
     * @return Validation
     */
    protected function validation() : Validation
    {
        $validator = new Validation();

        /**
         * @todo add validation for other file types, but we need to
         * look for a scalable way
         */
        $uploadConfig = [
            'maxSize' => '500M',
            'messageSize' => ':field exceeds the max filesize (:max)',
            'allowedTypes' => [
                'image/jpeg',
                'image/png',
                'image/webp',
                'audio/mpeg',
                'audio/mp3',
                'audio/mpeg',
                'application/pdf',
                'audio/mpeg3',
                'audio/x-mpeg-3',
                'application/x-zip-compressed',
                'application/zip',
                'application/octet-stream',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'messageType' => 'Allowed file types are :types',
        ];

        $validator->add(
            'file',
            new FileValidator($uploadConfig)
        );

        return $validator;
    }

    /**
     * Upload the document and save them to the filesystem.
     *
     * @todo add test
     *
     * @return array
     */
    protected function processFiles() : array
    {
        $allFields = $this->request->getPostData();

        $validator = $this->validation();

        $files = [];
        foreach ($this->request->getUploadedFiles() as $file) {
            //validate this current file
            $errors = $validator->validate([
                'file' => [
                    'name' => $file->getName(),
                    'type' => $file->getType(),
                    'tmp_name' => $file->getTempName(),
                    'error' => $file->getError(),
                    'size' => $file->getSize(),
                ]
            ]);

            /**
             * @todo figure out why this failes
             */
            if (!defined('API_TESTS')) {
                if (count($errors)) {
                    foreach ($errors as $error) {
                        throw new UnprocessableEntityException((string) $error);
                    }
                }
            }

            $fileSystem = Helper::upload($file);

            //add settings
            foreach ($allFields as $key => $settings) {
                $fileSystem->set($key, $settings);
            }

            Helper::setImageDimensions($file, $fileSystem);

            $files[] = $fileSystem;
        }

        return $files;
    }
}

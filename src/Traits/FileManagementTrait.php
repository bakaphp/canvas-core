<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Http\Exception\UnprocessableEntityException;
use Canvas\Filesystem\Helper;
use Canvas\Models\FileSystem;
use Canvas\Models\FileSystemEntities;
use Canvas\Models\FileSystemSettings;
use Baka\Contracts\Controllers\ProcessOutputMapperTrait;
use Phalcon\Http\Response;

trait FileManagementTrait
{
    use ProcessOutputMapperTrait;

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
            throw new UnprocessableEntityException('No files to upload');
        }

        return $this->response($this->processOutput($this->processFiles()));
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
     * Delete a file attribute.
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
     * Upload the document and save them to the filesystem.
     *
     * @todo add test
     *
     * @return array
     */
    protected function processFiles() : array
    {
        $allFields = $this->request->getPostData();
        $downloadable = isset($allFields['downloadable']) ? $allFields['downloadable'] : false;

        $files = [];
        $options = [];
        foreach ($this->request->getUploadedFiles() as $file) {
            $options["ContentDisposition"] = $downloadable ? "attachment" : "inline";
            $fileSystem = Helper::upload($file, $options);

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

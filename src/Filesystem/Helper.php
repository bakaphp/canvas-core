<?php

declare(strict_types=1);

namespace Canvas\Filesystem;

use Baka\Filesystem\Helper as FilesystemHelper;
use Baka\Validations\File as FileValidation;
use Canvas\Models\FileSystem;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use Phalcon\Http\Request\FileInterface;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Text;

class Helper extends FilesystemHelper
{
    /**
     * Given a file create it in the filesystem.
     *
     * @param \Phalcon\Http\Request\File $file
     *
     * @return FileSystem
     */
    public static function upload(FileInterface $file, array $options = []) : FileSystem
    {
        FileValidation::validate($file);

        $di = Di::getDefault();
        $config = $di->get('config');

        //get the filesystem config from app settings (local | s3)
        $appSettingFileConfig = $di->get('app')->get('filesystem');
        $fileSystemConfig = $config->filesystem->{$appSettingFileConfig};

        //create local filesystem , for temp files
        $di->get('filesystem', ['local'])->createDir($config->filesystem->local->path);

        //get the tem file
        $fileName = self::generateUniqueName($file, $config->filesystem->local->path . '/');
        $completeFilePath = $fileSystemConfig->path . DIRECTORY_SEPARATOR . $fileName;
        $uploadFileNameWithPath = $appSettingFileConfig === 'local' ? $fileName : $completeFilePath;

        /**
         * upload file base on temp.
         *
         * @todo change this to determine type of file and recreate it if its a image
         */
        $di->get('filesystem')->writeStream($uploadFileNameWithPath, fopen($file->getTempName(), 'r'), $options);

        $url = Text::reduceSlashes($fileSystemConfig->cdn . DIRECTORY_SEPARATOR . $uploadFileNameWithPath);
        return (new self)->addToFilesystem(
            $file->getName(),
            $di->get('userData')->currentCompanyId(),
            $di->get('app')->getId(),
            $di->get('userData')->getId(),
            Text::reduceSlashes($completeFilePath),
            $url,
            $file->getExtension(),
            (string) $file->getSize()
        );
    }

    /**
     * Given a image set its dimension.
     *
     * @param File $file
     * @param \Canvas\Models\FileSystem $fileSystem
     *
     * @return void
     */
    public static function setImageDimensions(FileInterface $file, FileSystem $fileSystem) : void
    {
        if (Helper::isImage($file)) {
            $image = new Gd($file->getTempName());
            $fileSystem->set('width', $image->getWidth());
            $fileSystem->set('height', $image->getHeight());
            $fileSystem->set(
                'orientation',
                $image->getHeight() > $image->getWidth() ? 'portrait' : 'landscape'
            );
        }
    }

    /**
     * uploadFile.
     *
     * Upload local file to s3 bucket
     *
     * @param  string $fileName
     * @param  int $fileName
     * @param  int $fileName
     * @param  array $options
     *
     * @return FileSystem
     */
    public static function uploadToS3(string $fileName, int $usersId, int $companiesId, array $options = []) : FileSystem
    {
        $di = Di::getDefault();
        $config = $di->get('config');
        $fileSystemConfig = $config->filesystem->s3;

        $localPath = $config->filesystem->local->path . '/' . $fileName;
        $completeFilePath = $fileSystemConfig->path . DIRECTORY_SEPARATOR . $fileName;
        $uploadFileNameWithPath = $completeFilePath;

        /**
         * upload file base on temp.
         *
         * @todo change this to determine type of file and recreate it if its a image
         */
        $di->get('filesystem')->writeStream($uploadFileNameWithPath, fopen($localPath, 'r'), $options);

        $url = Text::reduceSlashes($fileSystemConfig->cdn . DIRECTORY_SEPARATOR . $uploadFileNameWithPath);
        return (new self)->addToFilesystem(
            $fileName,
            $companiesId,
            $di->get('app')->getId(),
            $usersId,
            Text::reduceSlashes($completeFilePath),
            $url,
            end(explode('.', $fileName)),
            (string)filesize($localPath)
        );
    }

    /**
     * addToFilesystem.
     *
     * @param  string $name
     * @param  int $companiesId
     * @param  int $appId
     * @param  int $userId
     * @param  string $path
     * @param  string $url
     * @param  string $fileType
     * @param  int $size
     *
     * @return FileSystem
     */
    public function addToFilesystem(string $name, int $companiesId, int $appId, int $userId, string $path, string $url, string $fileType, string $size) : FileSystem
    {
        $fileSystem = new FileSystem();
        $fileSystem->name = $name;
        $fileSystem->companies_id = $companiesId;
        $fileSystem->apps_id = $appId;
        $fileSystem->users_id = $userId;
        $fileSystem->path = $path;
        $fileSystem->url = $url;
        $fileSystem->file_type = $fileType;
        $fileSystem->size = $size;

        $fileSystem->saveOrFail();

        //set the unique name we generate
        $uniqueName = (bool) Di::getDefault()->get('app')->get('unique_name_separator') ? DIRECTORY_SEPARATOR . $path : $path;
        $fileSystem->set('unique_name', Text::reduceSlashes($uniqueName));
        return $fileSystem;
    }
}

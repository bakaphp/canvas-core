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
    public static function upload(FileInterface $file) : FileSystem
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

        if ($appSettingFileConfig == 's3' && $fileSystemConfig->fileDownload) {
            $di->get('filesystem')->writeStream($uploadFileNameWithPath, fopen($file->getTempName(), 'r'), ['ContentDisposition' => 'attachment']);
        } else {
            $di->get('filesystem')->writeStream($uploadFileNameWithPath, fopen($file->getTempName(), 'r'));
        }
        
        $fileSystem = new FileSystem();
        $fileSystem->name = $file->getName();
        $fileSystem->companies_id = $di->get('userData')->currentCompanyId();
        $fileSystem->apps_id = $di->get('app')->getId();
        $fileSystem->users_id = $di->get('userData')->getId();
        $fileSystem->path = Text::reduceSlashes($completeFilePath);
        $fileSystem->url = Text::reduceSlashes($fileSystemConfig->cdn . DIRECTORY_SEPARATOR . $uploadFileNameWithPath);
        $fileSystem->file_type = $file->getExtension();
        $fileSystem->size = (string) $file->getSize();

        $fileSystem->saveOrFail();

        //set the unique name we generate
        $uniqueName = (bool) $di->get('app')->get('unique_name_separator') ? DIRECTORY_SEPARATOR . $uploadFileNameWithPath : $uploadFileNameWithPath;
        $fileSystem->set('unique_name', Text::reduceSlashes($uniqueName));

        return $fileSystem;
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
}

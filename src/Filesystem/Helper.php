<?php

declare(strict_types=1);

namespace Canvas\Filesystem;

use Canvas\Models\FileSystem;
use Exception;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Text;

class Helper
{
    /**
     * Generate a unique name in a specific dir.
     *
     * @param string $dir the specific dir where the file will be saved
     * @param bool $withPath
     *
     * @return string
     */
    public static function generateUniqueName(File $file, string $dir, $withPath = false) : string
    {
        // the provided path has to be a dir
        if (!is_dir($dir)) {
            throw new Exception("The dir provided: '{$dir}' isn't a valid one.");
        }

        $path = tempnam($dir . '/', '');

        //this function creates a file (like touch) so, we have to delete it.
        unlink($path);
        $uniqueName = $path;
        if (!$withPath) {
            $uniqueName = str_replace($dir, '', $path);
        }

        return $uniqueName . '.' . strtolower($file->getExtension());
    }

    /**
     * Create a File instance from a given path.
     *
     * @param string $path Path of the file to be used
     *
     * @return File
     */
    public static function pathToFile(string $path) : File
    {
        //Simulate the body of a Phalcon\Request\File class
        return new File([
            'name' => basename($path),
            'type' => mime_content_type($path),
            'tmp_name' => $path,
            'error' => 0,
            'size' => filesize($path),
        ]);
    }

    /**
     * Given a file create it in the filesystem.
     *
     * @param File $file
     *
     * @return bool
     */
    public static function upload(File $file) : FileSystem
    {
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
        $di->get('filesystem')->writeStream($uploadFileNameWithPath, fopen($file->getTempName(), 'r'));

        $fileSystem = new FileSystem();
        $fileSystem->name = $file->getName();
        $fileSystem->companies_id = $di->get('userData')->currentCompanyId();
        $fileSystem->apps_id = $di->get('app')->getId();
        $fileSystem->users_id = $di->get('userData')->getId();
        $fileSystem->path = $completeFilePath;
        $fileSystem->url = Text::reduceSlashes($fileSystemConfig->cdn . DIRECTORY_SEPARATOR . $uploadFileNameWithPath);
        $fileSystem->file_type = $file->getExtension();
        $fileSystem->size = $file->getSize();

        $fileSystem->saveOrFail();

        return $fileSystem;
    }

    /**
     * Is this file a image?
     *
     * @param File $file
     *
     * @return boolean
     */
    public static function isImage(File $file) : bool
    {
        return strpos(mime_content_type($file->getTempName()), 'image/') === 0;
    }

    /**
     * Given a image set its dimension.
     *
     * @param File $file
     * @param FileSystem $fileSystem
     *
     * @return void
     */
    public static function setImageDimensions(File $file, FileSystem $fileSystem) : void
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

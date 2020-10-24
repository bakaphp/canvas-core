<?php

namespace Canvas\Cli\Tasks;

use Canvas\Models\FileSystem;
use Phalcon\Cli\Task as PhTask;

/**
 * Class AclTask.
 *
 * @package Canvas\Cli\Tasks;
 *
 * @property \Canvas\Acl\Manager $acl
 * @property \Phalcon\Di $di
 */
class FileSystemTask extends PhTask
{
    /**
     * Create the default roles of the system.
     *
     * @return void
     */
    public function mainAction()
    {
        echo 'Main action for FileSystem Task';
    }

    /**
     * Default roles for the crm system.
     *
     * @return void
     */
    public function purgeImagesAction(int $fullDelete = 0, string $fileSystem) : void
    {
        //Option to fully delete or soft delete an image
        //$fullDelete = $params[0];

        // Specify the filesystem from which to erase
        //$fileSystem = $params[1];

        $detachedImages = FileSystem::find([
            'conditions' => 'users_id = 0 and is_deleted = 0'
        ]);

        if ($fullDelete == 0 && is_object($detachedImages)) {
            foreach ($detachedImages as $detachedImage) {
                //Get the file name
                $filePathArray = explode('/', $detachedImage->path);
                $fileName = end($filePathArray);

                //Soft Delete file
                $detachedImage->is_deleted = 1;

                if ($detachedImage->update()) {
                    $this->di->get('filesystem', $fileSystem)->delete($fileName);
                    echo 'Image with id ' . $detachedImage->id . " has been soft deleted \n";
                }
            }
        } else {
            foreach ($detachedImages as $detachedImage) {
                //Get the file name
                $filePathArray = explode('/', $detachedImage->path);
                $fileName = end($filePathArray);

                echo 'Image with id ' . $detachedImage->id . " has been fully deleted \n";
                $detachedImage->delete();
                $this->di->get('filesystem', $fileSystem)->delete($fileName);
            }
        }
    }
}

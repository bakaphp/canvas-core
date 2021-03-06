<?php

namespace Canvas\Providers;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class FileSystemProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->set(
            'filesystem',
            function ($filesystem = null) use ($container) {
                $config = $container->getShared('config');

                //we ened to call it internally to avoid the test failing WTF
                $app = $container->getShared('app');

                //if its null lets get the filesystem from the app settings
                if (is_null($filesystem)) {
                    $filesystem = $app->get('filesystem');
                }

                if ($filesystem === 'local') {
                    //create directory
                    $adapter = new Local($config->filesystem->local->path);
                } else {
                    //s3
                    $client = new S3Client($config->filesystem->s3->info->toArray());
                    $adapter = new AwsS3Adapter($client, $config->filesystem->s3->bucket, null, ['ACL' => 'public-read']);
                }
                return new Filesystem($adapter);
            }
        );
    }
}

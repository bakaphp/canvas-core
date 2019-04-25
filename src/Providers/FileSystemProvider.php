<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;

class FileSystemProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');
        $app = $container->getShared('app');

        $container->set(
            'filesystem',
            function ($filesystem = null) use ($config, $app) {
                //if its null lets get the filesystem from the app settings
                if (is_null($filesystem)) {
                    $filesystem = $app->getSettings('filesystem');
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

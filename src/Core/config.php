<?php

use function Canvas\Core\appPath;
use function Canvas\Core\envValue;
use Canvas\Constants\Flags;

return [
    'application' => [ //@todo migration to app
        'production' => envValue('APP_ENV', 'development') == Flags::PRODUCTION ?: 0,
        'development' => getenv('DEVELOPMENT'),
        'jwtSecurity' => getenv('JWT_SECURITY'),
        'debug' => [
            'profile' => getenv('DEBUG_PROFILE'),
            'logQueries' => getenv('DEBUG_QUERY'),
            'logRequest' => getenv('DEBUG_REQUEST')
        ],
    ],
    'app' => [
        //GEWAER is a multi entity app encosystem so we need what is the current api ID for this api
        'id' => envValue('GEWAER_APP_ID', 1),
        'frontEndUrl' => envValue('FRONTEND_URL'),
        'version' => envValue('VERSION', time()),
        'timezone' => envValue('APP_TIMEZONE', 'UTC'),
        'debug' => envValue('APP_DEBUG', false),
        'env' => envValue('APP_ENV', 'development'),
        'production ' => envValue('APP_ENV', 'development') == Flags::PRODUCTION ?: 0,
        'logsReport' => envValue('APP_LOGS_REPORT', false),
        'devMode' => boolval(
            'development' === envValue('APP_ENV', 'development')
        ),
        'baseUri' => envValue('APP_BASE_URI'),
        'supportEmail' => envValue('APP_SUPPORT_EMAIL'),
        'time' => microtime(true),
        'namespaceName' => envValue('APP_NAMESPACE'),
        'subscription' => [
            'defaultPlan' => [
                'name' => 'default-free-trial'
            ]
        ]
    ],
    'filesystem' => [
        //temp directoy where we will upload our files before moving them to the final location
        'uploadDirectoy' => appPath(envValue('LOCAL_UPLOAD_DIR_TEMP')),
        'local' => [
            'path' => appPath(envValue('LOCAL_UPLOAD_DIR')),
            'cdn' => envValue('FILESYSTEM_CDN_URL'),
        ],
        /**
         * @todo move this to app settings config
         */
        's3' => [
            'info' => [
                'credentials' => [
                    'key' => getenv('S3_PUBLIC_KEY'),
                    'secret' => getenv('S3_SECRET_KEY'),
                ],
                'region' => getenv('S3_REGION'),
                'version' => getenv('S3_VERSION'),
            ],
            'path' => envValue('S3_UPLOAD_DIR'),
            'bucket' => getenv('S3_BUCKET'),
            'cdn' => envValue('S3_CDN_URL'),
        ],
    ],
    'cache' => [
        'data' => [
            'front' => [
                'adapter' => 'Data',
                'options' => [
                    'lifetime' => envValue('CACHE_LIFETIME'),
                ],
            ],
            'back' => [
                'dev' => [
                    'adapter' => 'File',
                    'options' => [
                        'cacheDir' => appPath('storage/cache/data/'),
                    ],
                ],
                'prod' => [
                    'adapter' => 'Libmemcached',
                    'options' => [
                        'servers' => [
                            [
                                'host' => envValue('DATA_API_MEMCACHED_HOST'),
                                'port' => envValue('DATA_API_MEMCACHED_PORT'),
                                'weight' => envValue('DATA_API_MEMCACHED_WEIGHT'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'metadata' => [
            'dev' => [
                'adapter' => 'Memory',
                'options' => [],
            ],
            'prod' => [
                'adapter' => 'Files',
                'options' => [
                    'metaDataDir' => appPath('storage/cache/metadata/'),
                ],
            ],
        ],
    ],
    'email' => [
        'driver' => 'smtp',
        'host' => envValue('EMAIL_HOST'),
        'port' => envValue('EMAIL_PORT'),
        'username' => envValue('EMAIL_USER'),
        'password' => envValue('EMAIL_PASS'),
        'from' => [
            'email' => envValue('EMAIL_FROM_PRODUCTION'),
            'name' => envValue('EMAIL_FROM_NAME_PRODUCTION'),
        ],
        'debug' => [
            'from' => [
                'email' => envValue('EMAIL_FROM_DEBUG'),
                'name' => envValue('EMAIL_FROM_NAME_DEBUG'),
            ],
        ],
    ],
    'beanstalk' => [
        'host' => getenv('BEANSTALK_HOST'),
        'port' => getenv('BEANSTALK_PORT'),
        'prefix' => getenv('BEANSTALK_PREFIX'),
    ],
    'elasticSearch' => [
        'hosts' => [getenv('ELASTIC_HOST')], //change to pass array
    ],
    'jwt' => [
        'secretKey' => envValue('APP_JWT_TOKEN'),
        'payload' => [
            'exp' => envValue('APP_JWT_SESSION_EXPIRATION', 1440),
            'iss' => 'phalcon-jwt-auth',
        ],
    ],
    'pusher' => [
        'id' => envValue('PUSHER_ID'),
        'key' => envValue('PUSHER_KEY'),
        'secret' => envValue('PUSHER_SECRET'),
        'cluster' => envValue('PUSHER_CLUSTER'),
    ],
    'stripe' => [
        'secretKey' => getenv('STRIPE_SECRET'),
        'secret' => getenv('STRIPE_SECRET'),
        'public' => getenv('STRIPE_PUBLIC'),
    ],
    'throttle' => [
        'bucketSize' => getenv('THROTTLE_BUCKET_SIZE'),
        'refillTime' => getenv('THROTTLE_REFILL_TIME'),
        'refillAmount' => getenv('THROTTLE_REFILL_AMOUNT'),
    ],
    'pushNotifications' => [
        'appId' => getenv('CANVAS_ONESIGNAL_APP_ID'),
        'authKey' => getenv('CANVAS_ONESIGNAL_AUTH_KEY'),
        'userAuthKey' => getenv('CANVAS_ONESIGNAL_APP_USER_AUTH_KEY')
    ]
];

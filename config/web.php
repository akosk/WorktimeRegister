<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id'         => 'basic',
    'basePath'   => dirname(__DIR__),
    'language'   => 'hu-HU',
    'bootstrap'  => ['log'],
    'components' => [
        'request'              => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'QLfjhC6KntLLjQvHsF-3yLs8RMYnay3Z',
        ],
        'cache'                => [
            'class' => 'yii\caching\FileCache',
        ],
        'user'                 => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler'         => [
            'errorAction' => 'site/error',
        ],
        'mailer'               => [
            'class'            => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log'                  => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
//        'authClientCollection' => [
//            'class'   => 'yii\authclient\Collection',
//            'clients' => [
//                'google'   => [
//                    'class' => 'yii\authclient\clients\GoogleOpenId'
//                ],
//                'facebook' => [
//                    'class'        => 'yii\authclient\clients\Facebook',
//                    'clientId'     => '587558988036691',
//                    'clientSecret' => '8c28f4586c3ea6734f8be844c4bdfcf6',
//                ],
//            ],
//        ],
        'authManager'          => [
            'class'        => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest'],
        ],
        'urlManager'           => [
            'enablePrettyUrl'     => true,
            'showScriptName'      => true,
            'enableStrictParsing' => false,
            'rules'               => [
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>'          => '<module>/<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>'              => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>'                       => '<controller>/<action>',
                ''                                                    => '/site/index',
            ],
        ],
        'view'                 => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@app/views/user'
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                '@app\components\assets\FontawesomeAsset' => [
                    'sourcePath' => '',
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'db'                   => require(__DIR__ . '/db.php'),
    ],
    'modules'    => [
        'user'       => [
            'class'                  => 'dektrium\user\Module',
            'components'             => [
                'manager' => [
                    'userClass' => 'app\models\User',
                    'profileClass' => 'app\models\Profile',
                    'loginFormClass' => 'app\models\LoginForm',
                ],
            ],
            'controllerMap' => [
                'security' => 'app\controllers\SecurityController',
                'admin' => 'app\controllers\AdminController'
            ],
            'enableUnconfirmedLogin' => true,
            'enableConfirmation'     => false,
            'confirmWithin'          => 21600,
            'cost'                   => 12,
            'admins'                 => ['emr1']
        ],
        'attendance' => [
            'class' => 'app\modules\attendance\Module',
        ],

        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ]
    ],
    'params'     => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;

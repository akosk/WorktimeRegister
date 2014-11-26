<?php

namespace app\modules\attendance;


class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\attendance\controllers';

    public function init()
    {
        parent::init();

        \Yii::$app->i18n->translations['attendance*'] = [
            'class'    => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . '/messages',
        ];

    }
}

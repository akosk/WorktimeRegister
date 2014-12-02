<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.10.
 * Time: 14:03
 */

namespace app\modules\attendance;

use yii\web\AssetBundle;

class AttendanceAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/attendance/assets';
    public $css = ['css/attendance.css'];
    public $js = [
        'js/attendance.js', 'js/directives.js', 'js/factories.js', 'js/filters.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'app\components\assetbundles\FontawesomeAsset',
        'app\components\assetbundles\AngularAsset',
        'app\components\assetbundles\UnderscoreAsset',
//        'app\components\assetbundles\AngularNgMaskAsset',
        'app\components\assetbundles\AngularNgRouteAsset'
    ];
}
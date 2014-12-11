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
        'js/attendance.module.js',
        'js/attendance.config.js',
        'js/attendanceController.js',
        'js/directives/akTimeMask.js',
        'js/directives/emptyToNull.js',
        'js/directives/focusTime.js',
        'js/factories/dataService.js',
        'js/factories/helpers.js',
        'js/filters/justDay.js'
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
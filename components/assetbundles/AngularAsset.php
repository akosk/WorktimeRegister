<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.11.
 * Time: 11:15
 */

namespace app\components\assetbundles;

use yii\web\AssetBundle;

class AngularAsset extends AssetBundle
{
    public $sourcePath = '@bower/angularjs';
    public $css = ['angular-csp.css'];
    public $js = ['angular.js'];
    public $depends = [];

}
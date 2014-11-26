<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.19.
 * Time: 14:08
 */


namespace app\components\assetbundles;

use yii\web\AssetBundle;

class AngularNgRouteAsset extends AssetBundle
{
    public $sourcePath = '@bower/angular-route';
    public $css = [];
    public $js = ['angular-route.js'];
    public $depends = [];

}
<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.19.
 * Time: 14:08
 */


namespace app\components\assetbundles;

use yii\web\AssetBundle;

class AngularNgMaskAsset extends AssetBundle
{
    public $sourcePath = '@bower/angular-ngMask';
    public $css = [];
    public $js = ['dist/ngMask.js'];
    public $depends = [];

}
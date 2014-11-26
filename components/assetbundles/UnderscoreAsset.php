<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.11.
 * Time: 11:15
 */

namespace app\components\assetbundles;

use yii\web\AssetBundle;

class UnderscoreAsset extends AssetBundle
{
    public $sourcePath = '@bower/underscore';
    public $css = [];
    public $js = ['underscore.js'];
    public $depends = [];

}
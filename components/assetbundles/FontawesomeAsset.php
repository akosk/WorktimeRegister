<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.11.
 * Time: 8:56
 */

namespace app\components\assetbundles;

use yii\web\AssetBundle;

class FontawesomeAsset extends AssetBundle
{
    public $sourcePath = '@bower/fontawesome';
    public $css = ['css/font-awesome.css'];
    public $js = [];
    public $depends = [];

}
<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.11.
 * Time: 8:56
 */

namespace app\components\assetbundles;

use yii\web\AssetBundle;

class BootstrapSweetAlertAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-sweetalert';
    public $css = ['lib/sweet-alert.css'];
    public $js = ['lib/sweet-alert.js'];
    public $depends = [];

}
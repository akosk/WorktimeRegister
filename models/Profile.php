<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.17.
 * Time: 14:45
 */


namespace app\models;

use dektrium\user\models\Profile as BaseProfile;
use yii\helpers\ArrayHelper;

class Profile extends BaseProfile
{


    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'taxnumber' => 'Adószám'
        ]);
    }


}
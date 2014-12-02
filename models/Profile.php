<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.17.
 * Time: 14:45
 */


namespace app\models;

use app\modules\attendance\models\Department;
use dektrium\user\models\Profile as BaseProfile;
use yii\helpers\ArrayHelper;

class Profile extends BaseProfile
{


    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'taxnumber' => 'Adószám',
            'department_id' => 'Szervezeti egység'
        ]);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id']);
    }


}
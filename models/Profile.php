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

    public function rules()
    {
        return [
            [['bio'], 'string'],
            [['public_email', 'gravatar_email'], 'email'],
            ['website', 'url'],
            [['taxnumber','name', 'public_email', 'gravatar_email', 'location', 'website'], 'string', 'max' => 255],
        ];
    }

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

    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['taxnumber'])) {
            $this->user->updateUserDepartmentIdAndName($this->user->id);
            $this->user->updateUserRoles($this->user->id);
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }


    public function getUser()
    {
        return $this->hasOne('\app\models\User', ['id' => 'user_id']);
    }
}
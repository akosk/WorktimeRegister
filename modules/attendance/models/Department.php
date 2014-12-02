<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "department".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 *
 * @property Profile[] $profiles
 */
class Department extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attendance', 'ID'),
            'code' => Yii::t('attendance', 'Code'),
            'name' => Yii::t('attendance', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['department_id' => 'id']);
    }
}

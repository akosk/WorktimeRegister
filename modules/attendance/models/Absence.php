<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "absence".
 *
 * @property integer $id
 * @property string $code
 * @property integer $user_id
 * @property string $date
 * @property string $create_time
 * @property integer $create_user
 *
 * @property User $user
 */
class Absence extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'absence';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'user_id', 'date', 'create_time', 'create_user'], 'required'],
            [['user_id', 'create_user'], 'integer'],
            [['date', 'create_time'], 'safe'],
            [['code'], 'string', 'max' => 10]
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
            'user_id' => Yii::t('attendance', 'User ID'),
            'date' => Yii::t('attendance', 'Date'),
            'create_time' => Yii::t('attendance', 'Create Time'),
            'create_user' => Yii::t('attendance', 'Create User'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}

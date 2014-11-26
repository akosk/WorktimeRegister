<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "attendance".
 *
 * @property integer $id
 * @property string $date
 * @property string $start
 * @property string $end
 * @property integer $user_id
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property User $updateUser
 * @property User $user
 * @property User $createUser
 */
class Attendance extends \yii\db\ActiveRecord
{

    const WORKDAY="WORKDAY";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attendance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'user_id', 'create_time', 'create_user_id'], 'required'],
            [['date', 'start', 'end', 'create_time', 'update_time'], 'safe'],
            [['user_id', 'create_user_id', 'update_user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attendance', 'Azonosító'),
            'date' => Yii::t('attendance', 'Dátum'),
            'start' => Yii::t('attendance', 'Munkaidő kezdete'),
            'end' => Yii::t('attendance', 'Munkaidő vége'),
            'user_id' => Yii::t('attendance', 'Felhasználó'),
            'create_time' => Yii::t('attendance', 'Létrehozás ideje'),
            'create_user_id' => Yii::t('attendance', 'Létrehozta'),
            'update_time' => Yii::t('attendance', 'Módosítás ideje'),
            'update_user_id' => Yii::t('attendance', 'Módosította'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(User::className(), ['id' => 'update_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(User::className(), ['id' => 'create_user_id']);
    }
}

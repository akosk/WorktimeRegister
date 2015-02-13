<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "custom_workday".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $date
 * @property string $create_time
 * @property integer $create_user
 * @property string $type
 */
class CustomWorkday extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'custom_workday';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'date', 'create_time', 'create_user', 'type'], 'required'],
            [['user_id', 'create_user'], 'integer'],
            [['date', 'create_time'], 'safe'],
            [['type'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('attendance', 'ID'),
            'user_id'     => Yii::t('attendance', 'User ID'),
            'date'        => Yii::t('attendance', 'Date'),
            'create_time' => Yii::t('attendance', 'Create Time'),
            'create_user' => Yii::t('attendance', 'Create User'),
            'type'        => Yii::t('attendance', 'Type'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->type == "HOLIDAY") {
            Attendance::deleteAll('date=:date AND user_id=:user_id',
                [':date'    => $this->date,
                 ':user_id' => $this->user_id]);
        }
        parent::afterSave($insert, $changedAttributes);
    }


}

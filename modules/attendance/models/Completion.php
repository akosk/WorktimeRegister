<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "completion".
 *
 * @property integer $id
 * @property integer $year
 * @property integer $month
 * @property integer $user_id
 *
 * @property User $user
 */
class Completion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'completion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month', 'user_id'], 'required'],
            [['year', 'month', 'user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attendance', 'ID'),
            'year' => Yii::t('attendance', 'Year'),
            'month' => Yii::t('attendance', 'Month'),
            'user_id' => Yii::t('attendance', 'User ID'),
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

<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "red_letter_day".
 *
 * @property integer $id
 * @property string $date
 * @property string $type
 */
class RedLetterDay extends \yii\db\ActiveRecord
{
    const WORKING_DAY="WORKING_DAY";
    const HOLIDAY="HOLIDAY";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'red_letter_day';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'type'], 'required'],
            [['date'], 'safe'],
            [['type'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attendance', 'ID'),
            'date' => Yii::t('attendance', 'Dátum'),
            'type' => Yii::t('attendance', 'Ünnepnap'),
        ];
    }
}

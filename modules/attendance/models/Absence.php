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
    public static $ABSENCES = [
        '25004' => 'TERHESSÉGI GYERMEKÁGYI SEGÉLY',
        '25005' => 'GYERMEKGONDOZÁSI DÍJ',
        '25008' => 'TÁPPÉNZ, EGYÉB KERESŐKÉPTELENSÉG',
        '26002' => 'APÁKAT MEGILLETŐ MUNKAIDŐKEDVEZMÉNY',
        '91001' => 'RENDES SZABADSÁG',
        '91003' => 'TANULMÁNYI SZABADSÁG ILLETMÉNNYEL',
        '91004' => 'FIZETÉS NÉLKÜLI SZABADSÁG',
        '91009' => 'GYERMEKGONDOZÁSI SEGÉLY',
        '91011' => 'RENDKÍVÜLI SZABADSÁG',
        '93001' => 'IGAZOLATLAN TÁVOLLÉT',
        '93009' => 'FELMENTÉSI IDŐ',
        '93026' => 'IGAZOLT TÁVOLLÉT',
        '93030' => 'CSÚSZTATÁS (TÚLÓRA/TÚLMUNKA MIATT)',
        '93031' => 'PIHENŐNAP/SZABADNAP',
        '93032' => 'TOVÁBBKÉPZÉS, BELFÖLDI KIKÜLDETÉS',
    ];

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
            'id'          => Yii::t('attendance', 'ID'),
            'code'        => Yii::t('attendance', 'Code'),
            'user_id'     => Yii::t('attendance', 'User ID'),
            'date'        => Yii::t('attendance', 'Date'),
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

<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "close_month".
 *
 * @property integer $id
 * @property integer $department_id
 * @property integer $year
 * @property integer $month
 * @property integer $attendances_closed
 * @property integer $absences_closed
 */
class CloseMonth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'close_month';
    }

    /**
     * @param $year
     * @param $month
     * @param $department_id
     * @return CloseMonth|array|null|\yii\db\ActiveRecord
     */
    public static function findCloseMonth($year, $month, $department_id)
    {
        $closeMonth = CloseMonth::find()->where('year=:year AND month=:month AND department_id=:department_id',
            [
                ':year'          => $year,
                ':month'         => $month,
                ':department_id' => $department_id
            ])->one();
        if (!$closeMonth) $closeMonth = new CloseMonth();
        return $closeMonth;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['department_id', 'year', 'month'], 'required'],
            [['department_id', 'year', 'month', 'attendances_closed', 'absences_closed'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('attendance', 'ID'),
            'department_id'      => Yii::t('attendance', 'Department ID'),
            'year'               => Yii::t('attendance', 'Year'),
            'month'              => Yii::t('attendance', 'Month'),
            'attendances_closed' => Yii::t('attendance', 'Attendances Closed'),
            'absences_closed'    => Yii::t('attendance', 'Absences Closed'),
        ];
    }

    public static function isAttendancesClosed($year, $month, $department_id)
    {
        return self::findCloseMonth($year, $month, $department_id)->attendances_closed == 1;
    }

    public static function isAbsencesClosedDay($year, $month, $department_id)
    {
        $closeMonth = self::findCloseMonth($year, $month, $department_id);
        $date=$closeMonth->absences_close_time;
        $day = date('d', strtotime($date));
        return intval($day);

    }


    public static function isAbsencesClosed($year, $month, $department_id)
    {
        return self::findCloseMonth($year, $month, $department_id)->absences_closed == 1;
    }

}

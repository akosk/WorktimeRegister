<?php

namespace app\modules\attendance\models;

use Yii;

/**
 * This is the model class for table "user_import".
 *
 * @property integer $id
 * @property string $taxnumber
 * @property string $relationship
 * @property string $num
 * @property string $name_prefix
 * @property string $name
 * @property string $reference_number
 * @property string $department_code
 * @property string $department_name
 * @property string $group
 * @property integer $admin
 */
class UserImport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_import';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin'], 'integer'],
            [['taxnumber', 'relationship', 'num', 'name_prefix', 'name', 'reference_number', 'department_code', 'department_name', 'group'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attendance', 'ID'),
            'taxnumber' => Yii::t('attendance', 'Adószám'),
            'relationship' => Yii::t('attendance', 'Jogviszony'),
            'num' => Yii::t('attendance', 'Sorszám'),
            'name_prefix' => Yii::t('attendance', 'Előnév'),
            'name' => Yii::t('attendance', 'Név'),
            'reference_number' => Yii::t('attendance', 'Törzsszám'),
            'department_code' => Yii::t('attendance', 'Szervezeti egység kód'),
            'department_name' => Yii::t('attendance', 'Szervezeti egység név'),
            'group' => Yii::t('attendance', 'Állomány - csoport'),
            'admin' => Yii::t('attendance', 'Vezető'),
        ];
    }
}

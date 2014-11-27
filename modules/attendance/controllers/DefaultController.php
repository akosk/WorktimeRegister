<?php

namespace app\modules\attendance\controllers;

use app\components\assetbundles\FontawesomeAsset;
use app\components\LdapManager;
use app\models\User;
use app\modules\attendance\AttendanceAsset;
use app\modules\attendance\models\Attendance;
use app\moduls\attendance\models\Absence;
use app\moduls\attendance\models\RedLetterDay;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;

class DefaultController extends Controller
{

    public function behaviors()
    {
        return [

            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'get-attendances', 'save-attendances', 'set-red-letter-day',
                            'set-absence', 'remove-absence'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        \Yii::$app->assetManager->forceCopy = true;

        AttendanceAsset::register($this->getView());

        $user = User::findOne(\Yii::$app->user->id);

        return $this->render('index', [
            'user' => $user
        ]);
    }


    public function actionGetAttendances($year, $month, $user_id = null)
    {
        $data = [];
        $redLetterDays = RedLetterDay::find()->where('YEAR(date)=:year AND MONTH(date)=:month',
            [':year' => $year, ':month' => $month])->all();

        foreach ($redLetterDays as $item) {
            $data['attendances'][$item->date] = [
                'date'        => $item->date,
                'from'        => null,
                'to'          => null,
                'workDay'     => $item->type == RedLetterDay::WORKING_DAY,
                'userWorkDay' => $item->type == RedLetterDay::WORKING_DAY,
            ];
        }

        $absences = Absence::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
            [':year' => $year, ':month' => $month, ':userId' => \Yii::$app->user->id])->all();

        foreach ($absences as $item) {
            $data['attendances'][$item->date] = ArrayHelper::merge($data['attendances'][$item->date], [
                'date'            => $item->date,
                'from'            => null,
                'to'              => null,
                'userWorkDay'     => false,
                'userAbsenceCode' => $item->code
            ]);
        }

        $attendances = Attendance::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
            [':year' => $year, ':month' => $month, ':userId' => \Yii::$app->user->id])->all();


        foreach ($attendances AS $item) {
            $data['attendances'][$item->date] = ArrayHelper::merge($data['attendances'][$item->date], [
                'date' => $item->date,
                'from' => $item->start ? date("H:i", strtotime($item->start)) : null,
                'to'   => $item->end ? date("H:i", strtotime($item->end)) : null
            ]);
        }


        if (isset($data['attendances'])) {
            $data['attendances'] = array_values($data['attendances']);
        }
        echo Json::encode($data);
    }

    public function actionSaveAttendances()
    {
        $json = file_get_contents("php://input");
        $attendances = Json::decode($json);
        foreach ($attendances AS $item) {
            if ($item['absence'] == Attendance::WORKDAY || !isset($item['absence'])) {
                $attendance = Attendance::find()->where('date=:date AND user_id=:userId',
                    [':date' => $item['date'], ':userId' => \Yii::$app->user->id])->one();
                if (!$attendance) {
                    if (!($item['from'] || $item['end'])) {
                        continue;
                    }
                    $attendance = new Attendance();
                    $attendance->user_id = \Yii::$app->user->id;
                    $attendance->create_user_id = \Yii::$app->user->id;
                    $attendance->create_time = new \yii\db\Expression('NOW()');
                }

                $attendance->date = $item['date'];

                $attendance->start = $item['from'] ? $item['date'] . " " . $item['from'] : null;
                $attendance->end = $item['to'] ? $item['date'] . " " . $item['to'] : null;
                $attendance->save();

            }
        }
        echo "OK";
    }

    public function actionSetRedLetterDay()
    {
        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data)) {
            if (isset($data['date']) && isset($data['type'])) {
                $redLetterDay = RedLetterDay::find()->where('date=:date', [':date' => $data['date']])->one();
                if (!$redLetterDay) {
                    $redLetterDay = new RedLetterDay();
                    $redLetterDay->date = $data['date'];
                }
                $redLetterDay->type = $data['type'];
                $redLetterDay->save();
            } else
                if (isset($data['date']) && isset($data['delete'])) {
                    RedLetterDay::deleteAll('date=:date', [':date' => $data['date']]);
                }
        }
        echo "OK";
    }

    public function actionSetAbsence()
    {
        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data)) {
            $absence = Absence::find()->where('user_id=:user_id && date=:date', [
                ':user_id' => \Yii::$app->user->id,
                ':date'    => $data['date']])->one();
            if (!$absence) {
                $absence = new Absence();
                $absence->date = $data['date'];
                $absence->user_id = \Yii::$app->user->id;
            }
            $absence->code = $data['code'];
            $absence->create_time = new \yii\db\Expression('NOW()');
            $absence->create_user = \Yii::$app->user->id;
            $absence->save();
        }
        echo "OK";

    }

    public function actionRemoveAbsence()
    {
        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data) && isset($data['date'])) {
            $absence = Absence::deleteAll('user_id=:user_id && date=:date', [
                ':user_id' => \Yii::$app->user->id,
                ':date'    => $data['date']]);
        }
        echo "OK";

    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}

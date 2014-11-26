<?php

namespace app\modules\attendance\controllers;

use app\components\assetbundles\FontawesomeAsset;
use app\components\LdapManager;
use app\models\User;
use app\modules\attendance\AttendanceAsset;
use app\modules\attendance\models\Attendance;
use yii\filters\AccessControl;
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
                        'actions' => ['index', 'get-attendances','save-attendances'],
                        'allow' => true,
                        'roles' => ['@'],
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
        $attendances = Attendance::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
            [':year' => $year, ':month' => $month, ':userId' => \Yii::$app->user->id])->all();

        $data = [];
        foreach ($attendances AS $item) {
            $data['attendances'][] = [
                'date' => $item->date,
                'from' => $item->start ? date("H:i", strtotime($item->start)) : null,
                'to'   => $item->end ? date("H:i", strtotime($item->end)) : null
            ];
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

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}

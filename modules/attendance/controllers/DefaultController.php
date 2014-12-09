<?php

namespace app\modules\attendance\controllers;

use app\components\assetbundles\FontawesomeAsset;
use app\components\DateHelper;
use app\components\LdapManager;
use app\models\User;
use app\modules\attendance\AttendanceAsset;
use app\modules\attendance\models\Attendance;
use app\modules\attendance\models\Absence;
use app\modules\attendance\models\CloseMonth;
use app\modules\attendance\models\Completion;
use app\modules\attendance\models\Department;
use app\modules\attendance\models\RedLetterDay;
use app\modules\attendance\models\UserImport;
use app\modules\attendance\models\UserImportSearch;
use app\modules\attendance\models\UserSearch;
use DateInterval;
use DateTime;
use PHPExcel_Reader_Excel5;
use Yii;
use yii\base\Event;
use yii\caching\ChainedDependency;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rbac\Role;
use yii\web\Controller;
use yii\web\HttpException;


class DefaultController extends Controller
{
    public $year;
    public $month;

    public function behaviors()
    {
        return [

            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'get-attendances', 'save-attendances', 'set-red-letter-day',
                            'set-absence', 'remove-absence', 'admin', 'import', 'close'],
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

        $currentUser = User::findOne(\Yii::$app->user->id);

        $data['absences_closed'] = CloseMonth::isAbsencesClosed($year, $month, $currentUser->profile->department->id);
        $data['attendances_closed'] = CloseMonth::isAttendancesClosed($year, $month,
            $currentUser->profile->department->id);

        echo Json::encode($data);
    }

    public function actionSaveAttendances()
    {
        $json = file_get_contents("php://input");
        $attendances = Json::decode($json);

        $currentUser = User::findOne(\Yii::$app->user->id);

        if (count($attendances) > 0) {
            $dtime = DateTime::createFromFormat("Y-m-d", $attendances[0]['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            if (CloseMonth::isAbsencesClosed(
                $year,
                $month,
                $currentUser->profile->department->id
            )
            ) {
                throw new HttpException(403, 'A hónap zárolva van.');
            }

        }

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

        if (count($attendances) > 0) {
            $dtime = DateTime::createFromFormat("Y-m-d", $attendances[0]['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $this->checkIsMonthCompleted(\Yii::$app->user->id, $year, $month);
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

            $currentUser = User::findOne(\Yii::$app->user->id);

            if (CloseMonth::isAbsencesClosed(
                date("Y", strtotime($data['date'])),
                date("n", strtotime($data['date'])),
                $currentUser->profile->department->id
            )
            ) {
                throw new HttpException(403, 'A hónap zárolva van.');
            }

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

            $dtime = DateTime::createFromFormat("Y-m-d", $data['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            $this->checkIsMonthCompleted(\Yii::$app->user->id, $year, $month);

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


            $dtime = DateTime::createFromFormat("Y-m-d", $data['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            $this->checkIsMonthCompleted(\Yii::$app->user->id, $year, $month);
        }
        echo "OK";

    }

    public function actionAdmin()
    {
        $year = isset($_GET['year']) ? $_GET['year'] : date("Y");
        $month = isset($_GET['month']) ? $_GET['month'] : date("m");

        $currentUser = User::findOne(\Yii::$app->user->id);

        $closeMonth = CloseMonth::find()->where('year=:year AND month=:month AND department_id=:department_id',
            [
                ':year'          => $year,
                ':month'         => $month,
                ':department_id' => $currentUser->profile->department->id,
            ])->one();
        if (!$closeMonth) $closeMonth = new CloseMonth();


        $userSearch = new UserSearch();
        $userSearch->year = $year;
        $userSearch->month = $month;
        $dataProvider = $userSearch->search(\Yii::$app->request->queryParams);

        $q = "SELECT COUNT(*) FROM user t
            INNER JOIN profile p ON p.user_id=t.id
            LEFT JOIN completion c ON c.user_id=t.id AND c.year=:year AND c.month=:month
            WHERE p.department_id=:department_id AND c.id IS NULL";
        $db = \Yii::$app->db->createCommand($q, [':year'          => $year, ':month' => $month,
                                                 ':department_id' => 1])->queryScalar();
        $hasIncompleteUser = $db > 0;


        FontawesomeAsset::register($this->getView());


        return $this->render('admin', [
            'dataProvider'      => $dataProvider,
            'userSearch'        => $userSearch,
            'currentUser'       => $currentUser,
            'year'              => $year,
            'month'             => $month,
            'monthName'         => DateHelper::getMonthName($month),
            'nextMonthsYear'    => $month == 12 ? $year + 1 : $year,
            'nextMonth'         => $month == 12 ? 1 : $month + 1,
            'prevMonthsYear'    => $month == 1 ? $year - 1 : $year,
            'prevMonth'         => $month == 1 ? 12 : $month - 1,
            'hasIncompleteUser' => $hasIncompleteUser,
            'closeMonth'        => $closeMonth,
        ]);

    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    private function checkIsMonthCompleted($id, $year, $month)
    {
        if ($id && $year && $month) {
            $attendances = Attendance::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
                [':year' => $year, ':month' => $month, ':userId' => \Yii::$app->user->id])->orderBy('date')->all();
            $redLetterDays = RedLetterDay::find()->where('YEAR(date)=:year AND MONTH(date)=:month',
                [':year' => $year, ':month' => $month])->orderBy('date')->all();
            $absences = Absence::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
                [':year' => $year, ':month' => $month, ':userId' => \Yii::$app->user->id])->orderBy('date')->all();

            $date = new \DateTime();
            $date->setDate($year, $month, 1);

            $fail = false;
            do {
                $absence = $this->findDate($absences, $date);
                $redLetterDay = $this->findDate($redLetterDays, $date);

                if ($this->isWorkDay($date, $absence, $redLetterDay)) {
                    $attendance = $this->findDate($attendances, $date);
                    if (!$attendance || $attendance->start == '' || $attendance->end == '') {
                        $fail = true;
                        break;
                    }
                }

                $date->add(new DateInterval('P1D'));
            } while (!$fail && $date->format('m') == $month);

            if ($fail) {
                Completion::deleteAll('year=:year AND month=:month AND user_id=:user_id', [
                    ':year'    => $year,
                    ':month'   => $month,
                    ':user_id' => \Yii::$app->user->id,
                ]);
            } else {
                $completion = Completion::find()->where('year=:year AND month=:month AND user_id=:user_id', [
                    ':year'    => $year,
                    ':month'   => $month,
                    ':user_id' => \Yii::$app->user->id,
                ])->one();
                if (!$completion) {
                    $completion = new Completion();
                    $completion->user_id = \Yii::$app->user->id;
                    $completion->year = $year;
                    $completion->month = $month;
                    $completion->save();
                }
            }
        }
    }

    public function findDate($arr, $date)
    {
        foreach ($arr AS $item) {
            $cDate = $date->format('Y-m-d');
            if ($item->date == $cDate) {
                return $item;
            }
        }
        return null;
    }

    public function isWorkDay($date, $absence, $redLetterDay)
    {
        if ($absence)
            return false;
        if ($redLetterDay)
            return $redLetterDay->type == RedLetterDay::WORKING_DAY;

        $isWeekday = $date->format('N') < 6;
        return $isWeekday;

    }

    public function actionImport()
    {

        if (isset($_FILES['attachment'])) {

            $objReader = new PHPExcel_Reader_Excel5();

            /** Load $inputFileName to a PHPExcel Object  **/
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($_FILES['attachment']['tmp_name']);

            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $count = count($sheetData) - 1;

            UserImport::deleteAll();
            $imported = 0;
            $error = 0;
            for ($i = 2; $i <= count($sheetData); $i++) {
                $userImport = new UserImport();
                $userImport->taxnumber = (string)$sheetData[$i]['A'];
                $userImport->relationship = $sheetData[$i]['B'];
                $userImport->num = $sheetData[$i]['C'];
                $userImport->name_prefix = $sheetData[$i]['D'];
                $userImport->name = $sheetData[$i]['E'];
                $userImport->reference_number = $sheetData[$i]['F'];
                $userImport->department_code = $sheetData[$i]['G'];
                $userImport->department_name = $sheetData[$i]['H'];
                $userImport->group = $sheetData[$i]['I'];
                $userImport->admin = count($sheetData[$i]['J']) == 0 ? 0 : 1;
                if ($userImport->save()) {
                    $imported++;
                } else {
                    $error++;
                }
            }

//            Szervezeti egységek törzs frissítése

            $q = "SELECT DISTINCT t.department_code,t.department_name,d.id FROM user_import t
                 LEFT JOIN department d ON d.code=t.department_code ";

            $departments = Yii::$app->db->createCommand($q)->queryAll();
            for ($i = 0; $i < count($departments); $i++) {
                if (!$departments[$i]['id']) {
                    $department = new Department();
                    $department->code = $departments[$i]['department_code'];
                    $department->name = ucfirst(
                        mb_strtolower($departments[$i]['department_name'],
                            mb_detect_encoding($departments[$i]['department_name']))
                    );
                    $department->save(false);
                }
            }

            $user = new User();
            $user->updateUserDepartmentId();
            $user->updateUserRoles();

            if ($imported == $count) {
                Yii::$app->getSession()->setFlash('success', "<strong>Kész!</strong> $count felhasználó importálása sikeresen megtörtént.");
            } else {
                Yii::$app->getSession()->setFlash('error', "<strong>Hiba!</strong> {$count}/{$imported} felhasználó
                került importálásra.");
            }


        }


        $userImportSearch = new UserImportSearch();
        $dataProvider = $userImportSearch->search(\Yii::$app->request->queryParams);

        return $this->render('import', [
            'dataProvider'     => $dataProvider,
            'userImportSearch' => $userImportSearch,
        ]);

    }


    public function actionClose($year, $month, $target)
    {

//        TODO: Authorization

        $user = User::findOne(\Yii::$app->user->id);

        $closeMonth = CloseMonth::find()->where('year=:year AND month=:month AND department_id=:department_id',
            [
                ':year'          => $year,
                ':month'         => $month,
                ':department_id' => $user->profile->department->id,
            ])->one();
        if (!$closeMonth) {
            $closeMonth = new CloseMonth();
            $closeMonth->year = $year;
            $closeMonth->month = $month;
            $closeMonth->department_id = $user->profile->department->id;
        }

        $target = strtolower($target) . '_closed';
        $closeMonth->$target = 1;

        if ($closeMonth->save()) {
            \Yii::$app->getSession()->setFlash('success', '<strong>Zárolva!</strong> A zárolás sikeresen megtörtént.');
        } else {
            \Yii::$app->getSession()->setFlash('error', '<strong>Hiba!</strong> A zárolás sikertelen.');
        }


        return \Yii::$app->getResponse()->redirect(Url::toRoute('/attendance/default/admin'));
    }



}

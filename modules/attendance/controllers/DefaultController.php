<?php

namespace app\modules\attendance\controllers;

use app\components\assetbundles\BootstrapSweetAlertAsset;
use app\components\assetbundles\FontawesomeAsset;
use app\components\DateHelper;
use app\components\LdapManager;
use app\models\User;
use app\modules\attendance\AttendanceAsset;
use app\modules\attendance\models\Attendance;
use app\modules\attendance\models\Absence;
use app\modules\attendance\models\CloseMonth;
use app\modules\attendance\models\Completion;
use app\modules\attendance\models\CustomWorkday;
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
use kartik\mpdf\Pdf;
use \yii\db\Expression;

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
                            'set-absence', 'remove-absence', 'admin', 'import', 'close',
                            'set-instructor-attendance', 'get-instructor-attendance',
                            'add-dep-admin', 'remove-dep-admin',
                            'report-attendance', 'report-attendances', 'report-holiday', 'report-absence',
                            'report-holiday-after-close', 'report-absence-after-close',
                            'set-custom-working-day'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ]
            ]
        ];
    }

    public function actionIndex($id = null)
    {
        if ($id === null) {
            $id = \Yii::$app->user->id;
        }
        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        \Yii::$app->assetManager->forceCopy = true;
        AttendanceAsset::register($this->getView());

        $user = User::findOne($id);
        $currentUser = User::findOne(Yii::$app->user->id);

        return $this->render('index', [
            'user'        => $user,
            'currentUser' => $currentUser,
            'userRoles'   => Yii::$app->authManager->getRolesByUser($id)
        ]);
    }

    public function actionGetAttendances($id, $year, $month)
    {
        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $data = $this->getAttendances($id, $year, $month);

        echo Json::encode($data);
    }

    public function actionSaveAttendances($id)
    {
        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $json = file_get_contents("php://input");
        $attendances = Json::decode($json);

        $currentUser = User::findOne($id);

        if (count($attendances) > 0) {
            $dtime = DateTime::createFromFormat("Y-m-d", $attendances[0]['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            if (CloseMonth::isAttendancesClosed(
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
                    [':date' => $item['date'], ':userId' => $id])->one();
                if (!$attendance) {
                    if (!($item['from'] || $item['end'])) {
                        continue;
                    }
                    $attendance = new Attendance();
                    $attendance->user_id = $id;
                    $attendance->create_user_id = \Yii::$app->user->id;
                    $attendance->create_time = new \yii\db\Expression('NOW()');
                }

                $attendance->date = $item['date'];
                $attendance->update_user_id = \Yii::$app->user->id;
                $attendance->update_time = new \yii\db\Expression('NOW()');

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
            $this->checkIsMonthCompleted($id, $year, $month);
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
            } else {
                if (isset($data['date']) && isset($data['delete'])) {
                    RedLetterDay::deleteAll('date=:date', [':date' => $data['date']]);
                }
            }
        }
        echo "OK";
    }

    public function actionSetCustomWorkingDay($id)
    {
        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data)) {
            if (isset($data['date']) && isset($data['type'])) {
                $rld = RedLetterDay::find()->where('date=:date',
                    [
                        ':date' => $data['date'],
                    ])->one();
                $customWorkday = CustomWorkday::find()->where('user_id=:id AND date=:date',
                    [
                        ':date' => $data['date'],
                        ':id'   => $id
                    ])->one();
                if (!$customWorkday) {
                    $customWorkday = new CustomWorkday();
                    $customWorkday->date = $data['date'];
                    $customWorkday->user_id = $id;
                    $customWorkday->create_user = \Yii::$app->user->id;
                    $customWorkday->create_time = new \yii\db\Expression('NOW()');
                }
                $customWorkday->type = $data['type'] ? 'WORKING_DAY' : 'HOLIDAY';
                if ($rld && $rld->type == $customWorkday->type) {
                    $customWorkday->delete();
                    Absence::deleteAll('user_id=:id AND date=:date',
                        [
                            ':date' => $data['date'],
                            ':id'   => $id
                        ]);
                    Attendance::deleteAll('user_id=:id AND date=:date',
                        [
                            ':date' => $data['date'],
                            ':id'   => $id
                        ]);
                } else {
                    $customWorkday->save();
                }
            }
        }
        echo "OK";
    }

    public function actionSetAbsence($id)
    {
        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data)) {

            $currentUser = User::findOne($id);

            $isAbsencesClosed = CloseMonth::isAbsencesClosed(
                date("Y", strtotime($data['date'])),
                date("n", strtotime($data['date'])),
                $currentUser->profile->department->id
            );

            if ($isAbsencesClosed) {
                $closeMonth = CloseMonth::findCloseMonth(
                    date("Y", strtotime($data['date'])),
                    date("n", strtotime($data['date'])),
                    $currentUser->profile->department->id
                );

                $closeDay = date('j',strtotime($closeMonth->absences_close_time));
                $absenceDay = date("n", strtotime($data['date']));
                $isBeforeAbsenceClose = $closeDay > $absenceDay;
                if ($isBeforeAbsenceClose) {
                    throw new HttpException(403, "A hónap zárolva van. (Zárolva: $closeDay Távollét: $absenceDay");
                }
            }

            $absence = Absence::find()->where('user_id=:user_id && date=:date', [
                ':user_id' => $id,
                ':date'    => $data['date']])->one();
            if (!$absence) {
                $absence = new Absence();
                $absence->date = $data['date'];
                $absence->user_id = $id;
            }
            $absence->code = $data['code'];
            $absence->create_time = new \yii\db\Expression('NOW()');
            $absence->create_user = \Yii::$app->user->id;
            $absence->save();

            $dtime = DateTime::createFromFormat("Y-m-d", $data['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            $this->checkIsMonthCompleted($id, $year, $month);
        }

        echo "OK";
    }

    public function actionRemoveAbsence($id)
    {
        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $json = file_get_contents("php://input");
        $data = Json::decode($json);
        if (is_array($data) && isset($data['date'])) {
            $absence = Absence::deleteAll('user_id=:user_id && date=:date', [
                ':user_id' => $id,
                ':date'    => $data['date']]);

            $dtime = DateTime::createFromFormat("Y-m-d", $data['date']);
            $timestamp = $dtime->getTimestamp();

            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);

            $this->checkIsMonthCompleted($id, $year, $month);
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
        if (!$closeMonth) {
            $closeMonth = new CloseMonth();
        }

        $userSearch = new UserSearch();
        $userSearch->year = $year;
        $userSearch->month = $month;
        $dataProvider = $userSearch->search(\Yii::$app->request->queryParams);

        $q = "SELECT COUNT(*) FROM user t
            INNER JOIN profile p ON p.user_id=t.id
            LEFT JOIN completion c ON c.user_id=t.id AND c.year=:year AND c.month=:month
            WHERE p.department_id=:department_id AND c.id IS NULL";
        $db = \Yii::$app->db->createCommand($q,
            [':year'          => $year, ':month' => $month,
             ':department_id' => $currentUser->profile->department_id]
        )->queryScalar();
        $hasIncompleteUser = $db > 0;

        FontawesomeAsset::register($this->getView());
        BootstrapSweetAlertAsset::register($this->getView());

        $holidayReportUrl = Url::to(ArrayHelper::merge(
            [
                '/attendance/default/report-holiday',
                'year'  => $year,
                'month' => $month
            ], $_GET));
        $absenceReportUrl = Url::to(ArrayHelper::merge([
            '/attendance/default/report-absence',
            'year'  => $year,
            'month' => $month

        ], $_GET));

        $holidayReportAfterCloseUrl = Url::to(ArrayHelper::merge(
            [
                '/attendance/default/report-holiday-after-close',
                'year'  => $year,
                'month' => $month
            ], $_GET));
        $absenceReportAfterCloseUrl = Url::to(ArrayHelper::merge([
            '/attendance/default/report-absence-after-close',
            'year'  => $year,
            'month' => $month

        ], $_GET));

        $attendancesReportUrl = Url::to(ArrayHelper::merge([
            '/attendance/default/report-attendances',
            'year'  => $year,
            'month' => $month

        ], $_GET));

        return $this->render('admin', [
            'dataProvider'               => $dataProvider,
            'userSearch'                 => $userSearch,
            'currentUser'                => $currentUser,
            'year'                       => $year,
            'month'                      => $month,
            'monthName'                  => DateHelper::getMonthName($month),
            'nextMonthsYear'             => $month == 12 ? $year + 1 : $year,
            'nextMonth'                  => $month == 12 ? 1 : $month + 1,
            'prevMonthsYear'             => $month == 1 ? $year - 1 : $year,
            'prevMonth'                  => $month == 1 ? 12 : $month - 1,
            'hasIncompleteUser'          => $hasIncompleteUser,
            'closeMonth'                 => $closeMonth,
            'canCloseAbsence'            => (Yii::$app->user->can('admin') || Yii::$app->user->can('dep_leader') ||
                Yii::$app->user->can('dep_admin')) && DateHelper::alreadyLast($year, $month, 16) ? '' : 'disabled',
            'canCloseAttendance'         => (Yii::$app->user->can('admin') || Yii::$app->user->can('dep_leader') ||
                Yii::$app->user->can('dep_admin')) ? '' : 'disabled',
            'holidayReportUrl'           => $holidayReportUrl,
            'absenceReportUrl'           => $absenceReportUrl,
            'holidayReportAfterCloseUrl' => $holidayReportAfterCloseUrl,
            'absenceReportAfterCloseUrl' => $absenceReportAfterCloseUrl,
            'attendancesReportUrl'       => $attendancesReportUrl
        ]);
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    private function checkIsMonthCompleted($id, $year, $month)
    {
        $userRoles = Yii::$app->authManager->getRolesByUser($id);
        if ($userRoles['instructor']) {
            return;
        }

        if ($id && $year && $month) {
            $attendances = Attendance::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
                [':year' => $year, ':month' => $month, ':userId' => $id])->orderBy('date')->all();
            $redLetterDays = RedLetterDay::find()->where('YEAR(date)=:year AND MONTH(date)=:month',
                [':year' => $year, ':month' => $month])->orderBy('date')->all();
            $absences = Absence::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
                [':year' => $year, ':month' => $month, ':userId' => $id])->orderBy('date')->all();

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
                    ':user_id' => $id,
                ]);
            } else {
                $completion = Completion::find()->where('year=:year AND month=:month AND user_id=:user_id', [
                    ':year'    => $year,
                    ':month'   => $month,
                    ':user_id' => $id,
                ])->one();
                if (!$completion) {
                    $completion = new Completion();
                    $completion->user_id = $id;
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
        if ($absence) {
            return false;
        }
        if ($redLetterDay) {
            return $redLetterDay->type == RedLetterDay::WORKING_DAY;
        }

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
                if ((string)$sheetData[$i]['A'] != '') {
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
                } else {
                    $count--;
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
            $user->updateUserDepartmentIdAndName();
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

        $target1 = strtolower($target) . '_closed';
        $closeMonth->$target1 = 1;
        $target2 = strtolower($target) . '_close_time';
        $closeMonth->$target2 = new \yii\db\Expression('NOW()');

        if ($closeMonth->save()) {
            \Yii::$app->getSession()->setFlash('success', '<strong>Zárolva!</strong> A zárolás sikeresen megtörtént.');
        } else {
            \Yii::$app->getSession()->setFlash('error', '<strong>Hiba!</strong> A zárolás sikertelen.');
        }

        return \Yii::$app->getResponse()->redirect(
            Url::toRoute([
                '/attendance/default/admin',
                'year'  => $year,
                'month' => $month])
        );
    }

    public function actionSetInstructorAttendance($id = null, $year, $month, $value)
    {
        if ($id === null) {
            $id = \Yii::$app->user->id;
        }

        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $user = User::findOne($id);

        if (strtolower($value) == 'false') {
            Completion::deleteAll('year=:year AND month=:month AND user_id=:user_id', [
                ':year'    => $year,
                ':month'   => $month,
                ':user_id' => $id,
            ]);
        } else {
            $completion = Completion::find()->where('year=:year AND month=:month AND user_id=:user_id', [
                ':year'    => $year,
                ':month'   => $month,
                ':user_id' => $id,
            ])->one();
            if (!$completion) {
                $completion = new Completion();
                $completion->user_id = $id;
                $completion->year = $year;
                $completion->month = $month;
                $completion->save();
            }
        }
    }

    public function actionGetInstructorAttendance($id = null, $year, $month)
    {
        if ($id === null) {
            $id = \Yii::$app->user->id;
        }

        if (!$this->hasRight($id)) {
            throw new HttpException(403, 'Nincs jogosultsága az oldal megtekintéséhez!');
        }

        $user = User::findOne($id);

        $completion = Completion::find()->where('year=:year AND month=:month AND user_id=:user_id', [
            ':year'    => $year,
            ':month'   => $month,
            ':user_id' => $id,
        ])->one();

        return Json::encode(['value' => $completion !== null]);
    }

    public function actionAddDepAdmin($id)
    {
        $auth = \Yii::$app->authManager;
        $dep_admin = $auth->getRole('dep_admin');
        $auth->assign($dep_admin, $id);

        return \Yii::$app->getResponse()->redirect(Url::toRoute('/attendance/default/admin'));
    }

    public function actionRemoveDepAdmin($id)
    {
        $auth = \Yii::$app->authManager;
        $dep_admin = $auth->getRole('dep_admin');
        $auth->revoke($dep_admin, $id);

        return \Yii::$app->getResponse()->redirect(Url::toRoute('/attendance/default/admin'));
    }

    public function actionReportAttendance($user_id, $year, $month)
    {

        $user = User::findOne($user_id);

        $aq = $user->getCompletionOfMonth($year, $month);
        $completion = $aq->one();
        $isCompleted = $completion != null;

        $data = $this->getReportData($user_id, $year, $month);

        $userRoles = Yii::$app->authManager->getRolesByUser($user_id);

        $content = $this->renderPartial('_report-attendance', [
            'user'              => $user,
            'isInstructor'      => isset($userRoles['instructor']),
            'year'              => $year,
            'monthName'         => DateHelper::getMonthName($month),
            'attendances'       => $data,
            'totalEllapsedTime' => $this->sumEllapsedTimeInHour($data),
            'isCompleted'       => $isCompleted,

        ]);

        $pdf = $this->createPdf($content, 'jelenlet');

        return $pdf->render();
    }

    public function actionReportAttendances($year, $month)
    {

        $currentUser = User::findOne(\Yii::$app->user->id);

        $closeMonth = CloseMonth::find()->where('year=:year AND month=:month AND department_id=:department_id',
            [
                ':year'          => $year,
                ':month'         => $month,
                ':department_id' => $currentUser->profile->department->id,
            ])->one();
        if (!$closeMonth) {
            $closeMonth = new CloseMonth();
        }

        $userSearch = new UserSearch();
        $userSearch->year = $year;
        $userSearch->month = $month;
        $dataProvider = $userSearch->search(\Yii::$app->request->queryParams);

        $users = $dataProvider->getModels();

        $content = "";
        foreach ($users as $user) {

            $aq = $user->getCompletionOfMonth($year, $month);
            $completion = $aq->one();
            $isCompleted = $completion != null;

            $data = $this->getReportData($user->id, $year, $month);

            $userRoles = Yii::$app->authManager->getRolesByUser($user->id);
            $content .= $this->renderPartial('_report-attendance', [
                    'user'              => $user,
                    'isInstructor'      => isset($userRoles['instructor']),
                    'year'              => $year,
                    'monthName'         => DateHelper::getMonthName($month),
                    'attendances'       => $data,
                    'totalEllapsedTime' => $this->sumEllapsedTimeInHour($data),
                    'isCompleted'       => $isCompleted,

                ]) . "<pagebreak />";
        }

        $pdf = $this->createPdf($content, 'jelenlet');

        return $pdf->render();
    }

    public function sumEllapsedTimeInHour($data)
    {
        $total = array_reduce($data, function ($carry, $item) {
            return $carry + $item['ellapsedTime'];
        }, 0);

        return round($total / 3600, 2);
    }

    public function actionReportHoliday($year, $month)
    {
        $aggregatedAbsences = $this->getAbsenceReport($year, $month, true);

        $content = $this->renderPartial('_report-holiday', [
            'year'      => $year,
            'monthName' => DateHelper::getMonthName($month),
            'absences'  => $aggregatedAbsences
        ]);

        $pdf = $this->createPdf($content, 'szabadsag');

        return $pdf->render();
    }

    public function actionReportAbsence($year, $month)
    {
        $aggregatedAbsences = $this->getAbsenceReport($year, $month, false);

        $content = $this->renderPartial('_report-absence', [
            'year'      => $year,
            'monthName' => DateHelper::getMonthName($month),
            'absences'  => $aggregatedAbsences
        ]);

        $pdf = $this->createPdf($content, 'tavollet');

        return $pdf->render();
    }

    public function actionReportHolidayAfterClose($year, $month)
    {
        $aggregatedAbsences = $this->getAbsenceReport($year, $month, true);

        $content = $this->renderPartial('_report-holiday', [
            'year'      => $year,
            'monthName' => DateHelper::getMonthName($month),
            'absences'  => $aggregatedAbsences
        ]);

        $pdf = $this->createPdf($content, 'szabadsag');

        return $pdf->render();
    }

    public function actionReportAbsenceAfterClose($year, $month)
    {
        $aggregatedAbsences = $this->getAbsenceReport($year, $month, false);

        $content = $this->renderPartial('_report-absence', [
            'year'      => $year,
            'monthName' => DateHelper::getMonthName($month),
            'absences'  => $aggregatedAbsences
        ]);

        $pdf = $this->createPdf($content, 'tavollet');

        return $pdf->render();
    }

    public function isContinous($absence, $row)
    {
        $sameUser = $absence['taxnumber'] == $row['taxnumber'];
        return intval(substr($absence['date_to'], -2)) + 1 == intval(substr($row['date'],
            -2)) && $absence['code'] == $row['code'] && $sameUser;
    }

    public function hasRight($userId)
    {
        if ($userId !== null) {
            if ($userId == Yii::$app->user->id) {
                return true;
            }

            return Yii::$app->user->can('admin') ||
            Yii::$app->user->can('dep_admin') ||
            Yii::$app->user->can('dep_leader') ||
            Yii::$app->user->can('payroll_manager');
        }
        return true;
    }

    /**
     * @param $id
     * @param $year
     * @param $month
     * @return array
     */
    public function getAttendances($id, $year, $month)
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

        $customWorkdays = CustomWorkday::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
            [':year' => $year, ':month' => $month, ':userId' => $id])->all();

        foreach ($customWorkdays as $item) {
            $data['attendances'][$item->date] = ArrayHelper::merge($data['attendances'][$item->date],
                [
                    'date'        => $item->date,
                    'userWorkDay' => $item->type == 'WORKING_DAY'
                ]);
        }

        $absences = Absence::find()->where('YEAR(date)=:year AND MONTH(date)=:month AND user_id=:userId',
            [':year' => $year, ':month' => $month, ':userId' => $id])->all();

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
            [':year' => $year, ':month' => $month, ':userId' => $id])->all();

        foreach ($attendances AS $item) {
            if (!isset($data['attendances'][$item->date])
                ||
                !isset($data['attendances'][$item->date]['userWorkDay'])
                ||
                $data['attendances'][$item->date]['userWorkDay'] === true
            ) {

                $ellapsedTime = $item->start && $item->end ? strtotime($item->end) - strtotime($item->start) : 0;
                $data['attendances'][$item->date] = ArrayHelper::merge($data['attendances'][$item->date], [
                    'date'         => $item->date,
                    'from'         => $item->start ? date("H:i", strtotime($item->start)) : null,
                    'to'           => $item->end ? date("H:i", strtotime($item->end)) : null,
                    'ellapsedTime' => $ellapsedTime,
                    'worktime'     => $item->start && $item->end ?
                        round($ellapsedTime / 3600, 2)
                        :
                        null
                ]);
            }
        }

        if (isset($data['attendances'])) {
            $data['attendances'] = array_values($data['attendances']);
        }

        $currentUser = User::findOne($id);

        $data['absences_closed'] = CloseMonth::isAbsencesClosed($year, $month, $currentUser->profile->department->id);
        $data['attendances_closed'] = CloseMonth::isAttendancesClosed($year, $month,
            $currentUser->profile->department->id);
        $data['absences_closed_day'] = CloseMonth::isAbsencesClosedDay($year, $month,
            $currentUser->profile->department->id);
        return $data;
    }

    /**
     * @param $year
     * @param $month
     * @param $iDay
     * @param $data
     * @return array
     */
    public function insertDate($year, $month, $iDay, &$data)
    {
        $m = strlen($month) == 1 ? '0' . $month : $month;
        $d = strlen($iDay) == 1 ? '0' . $iDay : $iDay;
        $dateStr = "$year-$m-$d";
        $data[] = [
            'date'        => $dateStr,
            'userWorkDay' => !DateHelper::isWeekEnd($dateStr),
            'weekend'     => DateHelper::isWeekEnd($dateStr)
        ];
    }

    /**
     * @param $user_id
     * @param $year
     * @param $month
     * @return array
     */
    public function getReportData($user_id, $year, $month)
    {
        $data = $this->getAttendances($user_id, $year, $month);
        $data = isset($data['attendances']) ? $data['attendances'] : [];
        uasort($data, function ($a, $b) {
            return $a['date'] < $b['date'] ? -1 : 1;
        });

        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $iDay = 1;
        foreach ($data as $item) {
            $currentDay = intval(substr($item['date'], -2));
            while ($currentDay > $iDay) {
                $this->insertDate($year, $month, $iDay, $data);
                $iDay++;
            }
            $iDay++;
        }

        for (; $iDay <= $days; $iDay++) {
            $this->insertDate($year, $month, $iDay, $data);
        }

        uasort($data, function ($a, $b) {
            return $a['date'] < $b['date'] ? -1 : 1;
        });
        return $data;
    }

    /**
     * @param $content
     * @return Pdf
     */
    public function createPdf($content, $filename)
    {
// setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'filename'    => $filename . '.pdf',
            'mode'        => Pdf::MODE_UTF8,
            // A4 paper format
            'format'      => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_DOWNLOAD,
            // your html content input
            'content'     => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile'     => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline'   => '.kv-heading-1{font-size:16px} body {font-size:9px;}',
            // set mPDF properties on the fly
            'options'     => ['title' => 'Munkaidő nyilvántartó'],
            // call mPDF methods on the fly
            'methods'     => [
                'SetHeader' => ['Munkaidő nyilvántartó'],
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);
        return $pdf;
    }

    /**
     * @param $year
     * @param $month
     * @return array
     */
    public function getAbsenceReport($year, $month, $holidays = false)
    {
        $holidaysOrNotSql = '';
        if ($holidays) {
            $holidaysOrNotSql = ' IN ';
        } else {
            $holidaysOrNotSql = ' NOT IN ';
        }

        $filters = [];
        $params = [
            ":year"  => $year,
            ":month" => $month
        ];

        if (!(Yii::$app->user->can('admin') || Yii::$app->user->can('payroll_manager'))) {
            $currentUser = User::findOne(\Yii::$app->user->id);
            $params[':department_id'] = $currentUser->profile->department_id;
            $filters[] = 'p.department_id=:department_id';
        }

        if (isset($_GET['UserSearch'])) {
            $userSearch = $_GET['UserSearch'];
            if ($userSearch['username'] != '') {
                $params[':username'] = '%' . $userSearch['username'] . '%';
                $filters[] = 'u.username LIKE :username';
            }
            if ($userSearch['profile.name'] != '') {
                $params[':name'] = '%' . $userSearch['profile.name'] . '%';
                $filters[] = 'p.name LIKE :name';
            }
            if ($userSearch['profile.taxnumber'] != '') {
                $params[':taxnumber'] = '%' . $userSearch['profile.taxnumber'] . '%';
                $filters[] = 'p.taxnumber LIKE :taxnumber';
            }
            if ($userSearch['profile.department.name'] != '') {
                $params[':dep_name'] = '%' . $userSearch['profile.department.name'] . '%';
                $filters[] = 'd.name LIKE :dep_name';
            }
        }

        $filters = implode(' AND ', $filters);
        if (strlen($filters) > 0) {
            $filters = ' AND ' . $filters;
        }

        $q = "
            SELECT t.code, t.user_id, t.date, p.name, p.taxnumber, d.name AS department_name
            FROM absence t
            INNER JOIN user u ON u.id=t.user_id
            INNER JOIN profile p ON p.user_id=t.user_id
            INNER JOIN department d ON p.department_id=d.id
            WHERE t.code {$holidaysOrNotSql} ('91001')
              AND YEAR(t.date)=:year AND MONTH(t.date)=:month
              {$filters}
            ORDER BY t.user_id, t.date
        ";

        $absences = Yii::$app->db->createCommand($q, $params)->queryAll();
        $aggregatedAbsences = [];
        $currentAbsence = null;
        foreach ($absences as $row) {
            if ($this->isContinous($currentAbsence, $row)) {
                $currentAbsence['date_to'] = $row['date'];
                $currentAbsence['days'] = $currentAbsence['days'] + 1;
            } else {
                if ($currentAbsence) {
                    $aggregatedAbsences[] = $currentAbsence;
                }
                $currentAbsence = [
                    'code'            => $row['code'],
                    'name'            => $row['name'],
                    'taxnumber'       => $row['taxnumber'],
                    'department_name' => $row['department_name'],
                    'date_from'       => $row['date'],
                    'date_to'         => $row['date'],
                    'days'            => 1,
                ];
            }
        }
        if ($currentAbsence) {
            $aggregatedAbsences[] = $currentAbsence;
            return $aggregatedAbsences;
        }
        return $aggregatedAbsences;
    }
}

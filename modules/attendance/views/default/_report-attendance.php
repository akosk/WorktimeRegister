<?php
/**
 * Created: Ákos Kiszely
 * Date: 2015.01.05.
 * Time: 15:24
 */
use app\components\DateHelper;
use app\modules\attendance\models\Absence;

?>

<div class="text-center">
    <h4>Jelenlét jelentés</h4>
    <h4><strong><?= $user->profile->name ?> - <?= $year . ". " . $monthName ?></strong></h4>
    <h5><?= $user->profile->department->name ?></h5>
</div>


<?php
if ($isInstructor) {
    echo $this->render('_report_attendance_instructor', [
        'isCompleted' => $isCompleted
    ]);
} else {
    echo $this->render('_report_attendance_worker', [
        'attendances' => $attendances,
        'totalEllapsedTime' => $totalEllapsedTime
    ]);

}
?>

<div>
    <strong> Alulírott <?=$user->profile->name ?> kijelentem, hogy a munkaidőmet a fenti időszakra teljesítettem.</strong>
</div>


<?php
echo $this->render('_footer',[
    'signatureLeft'=>'Szervezeti egység vezető',
    'signatureRight'=>'Dolgozó',
]);
?>


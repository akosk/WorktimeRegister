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
        'attendances' => $attendances
    ]);

}
?>

<div>
    <strong> Alulírott XY kijelentem, hogy a munkaidőmet a fenti időszakra teljesítettem.</strong>
</div>
<br/>
<br/>
<table style="width:100%">
    <tr>
        <td style="width:33%;text-align: center;border-bottom: 1px dotted #000"></td>
        <td style="width:33%;"></td>
        <td style="width:33%;text-align: center;border-bottom: 1px dotted #000"></td>
    </tr>
    <tr>
        <td style="text-align: center;">Szervezeti egység vezető aláírás</td>
        <td></td>
        <td style="text-align: center;">Dolgozó neve</td>
    </tr>
    </tr>
</table>
<br/>
<div> MISKOLC, <?= date('Y-m-d') ?></div>

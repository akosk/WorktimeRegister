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
<table class="table table-condensed table-bordered">
    <thead>
    <tr>
        <th>Dátum</th>
        <th>Érkezés ideje</th>
        <th>Távozás ideje</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($attendances as $item) { ?>
        <tr>
            <td><?= DateHelper::getDayWithDayName($item['date']) ?></td>
            <?php if (
                (isset($item['userWorkDay']) && $item['userWorkDay'] === true) ||
                (!isset($item['userWorkDay']))
            ) {
                ?>
                <td><?= $item['from'] ?></td>
                <td><?= $item['to'] ?></td>
            <?php } else { ?>
                <td colspan="2"><?= isset($item['userAbsenceCode']) ?
                        Absence::$ABSENCES[$item['userAbsenceCode']] : 'Munkaszüneti nap' ?></td>
            <?php } ?>
        </tr>
    <?php } ?>
    </tbody>
</table>
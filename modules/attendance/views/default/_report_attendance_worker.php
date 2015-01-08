<?php
/**
 * Created: Ákos Kiszely
 * Date: 2015.01.08.
 * Time: 8:14
 */
use app\components\DateHelper;
use app\modules\attendance\models\Absence;

?>

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

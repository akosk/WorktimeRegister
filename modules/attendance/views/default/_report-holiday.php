<?php
/**
 * Created: Ákos Kiszely
 * Date: 2015.01.07.
 * Time: 9:52
 */

use app\components\DateHelper;
use app\modules\attendance\models\Absence;

?>

<div class="text-center">
    <h4>Szabadság jelentés</h4>
    <h4><strong><?= $year . ". " . $monthName ?></strong></h4>
</div>
<table class="table table-condensed table-bordered">
    <thead>
    <tr>
        <th>Név</th>
        <th>Adószám</th>
        <th>Szervezeti egység</th>
        <th>Szabadság kezdete</th>
        <th>Szabadság vége</th>
        <th>Napok száma</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($absences as $item) { ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['taxnumber'] ?></td>
            <td><?= $item['department_full_name'] ?></td>
            <td><?= $item['date_from'] ?></td>
            <td><?= $item['date_to'] ?></td>
            <td><?= $item['days'] ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php echo $this->render('_footer', [
    'signatureLeft'=>'Kitöltésért felelős',
    'signatureRight'=>'Szervezeti egység vezető',
]); ?>
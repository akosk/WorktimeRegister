<?php
/**
 * Created: Ákos Kiszely
 * Date: 2015.01.07.
 * Time: 9:52
 */

use app\components\DateHelper;
use app\modules\attendance\models\Absence;

?>

<?php
$szervezeti_egyseg="";
foreach ($vezetok as $item){
    if(strlen($szervezeti_egyseg)>0){
        $szervezeti_egyseg=$szervezeti_egyseg.", ";
    }
    $szervezeti_egyseg=$szervezeti_egyseg.$item['szervezeti_egyseg'];
}
//$szervezeti_egyseg_nev = "Z";
if(strlen($szervezeti_egyseg)>0){
    $szervezeti_egyseg="(".$szervezeti_egyseg.")";
}

?>

    <div class="text-center">
        <h4>Távollét jelentés</h4>
        <h4><strong><?= $year . ". " . $monthName." ".$szervezeti_egyseg ?></strong></h4>
    </div>
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th>Név</th>
            <th>Adószám</th>
            <th>Hiányázás oka</th>
            <th>Szervezeti egység</th>
            <th>Hiányzás kezdete</th>
            <th>Hiányzás vége</th>
            <th>Napok száma</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($absences as $item) { ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td><?= $item['taxnumber'] ?></td>
                <td><?= Absence::$ABSENCES[$item['code']] ?></td>
                <td><?= $item['department_full_name'] ?></td>
                <td><?= $item['date_from'] ?></td>
                <td><?= $item['date_to'] ?></td>
                <td><?= $item['days'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div>
         Igazolom, hogy a fenti időszakban az egységnél
            alkalmazott és a mulasztás jelentésen név szerint nem szerepeltetett
            dolgozók munkaidejüket teljesítették, és a teljes havi illetményre jogosultak.
    </div>

<?php echo $this->render('_footer', [
    'signatureLeft'=>'Kitöltésért felelős',
    'signatureRight'=>'Szervezeti egység vezető',
]); ?>


<div>
<br><br>
</div>
<?php
foreach ($vezetok as $item){
?>
    <div>
        Igazolom, hogy <?=$year?>. év <?=$monthName?> hónapban a(z) <?=$item['szervezeti_egyseg']?> vezetője (<?=$item['nev']?>) havi munkaidejét teljesítette, a teljes havi illetményre jogosult.
    </div>
<?php
    echo $this->render('_footer', [
        'signatureLeft'=>'Kitöltésért felelős',
        'signatureRight'=>'Gazdálkodási egység vezető/munkáltatói jogkör gyakorlója',
    ]);
}
//$szervezeti_egyseg_nev = "Z";

?>

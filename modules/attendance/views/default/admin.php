<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.28.
 * Time: 12:46
 */

//use kartik\grid\GridView;

use kartik\widgets\AlertBlock;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = Yii::t('attendance', 'Jelenlétek és zárolások');
$this->params['breadcrumbs'][] = $this->title;

?>

    <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>Fejlesztés alatt!</strong> A menüpont jelenleg fejlesztés alatt van, elnézést az esetleges
        kellemetlenségekért.
    </div>

    <div class="row">
        <div class="col-md-8">
            <h2><?= $currentUser->profile->department->name ?></h2>
        </div>
        <div class="col-md-4 text-right">
            <h2>
                <a href="<?= Url::to(['/attendance/default/admin', 'year'  => $prevMonthsYear,
                                                                   'month' => $prevMonth]) ?>"
                   class="btn btn-success"><i
                        class="fa fa-angle-left"></i> Előző hónap
                </a>
                <a href="<?= Url::to(['/attendance/default/admin', 'year'  => $nextMonthsYear,
                                                                   'month' => $nextMonth]) ?>" class="btn
                                                               btn-success">Következő hónap <i class="fa
    fa-angle-right"></i></a>
            </h2>

        </div>
    </div>
    <br/>
<?php
echo AlertBlock::widget([
    'useSessionFlash' => true,
    'type'            => AlertBlock::TYPE_ALERT
]);
?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Zárolás
                </div>
                <div class="panel-body">

                    <?php if ($hasIncompleteUser) { ?>
                        <div class="alert alert-danger" role="alert"><strong><i class="fa fa-exclamation-triangle"></i>
                                Figyelem!</strong>
                            Van olyan
                            dolgozó aki még nem fejezte be a jelenléti ív kitöltését.
                        </div>
                    <?php } ?>


                    <?php if ($closeMonth->attendances_closed != 1) { ?>
                        <button id='attendances-close-btn' class="btn btn-danger">Jelenlétek zárolása</button>
                    <?php } else { ?>
                        <span class="btn disabled"><i class="fa fa-lock" role="alert"></i> A jelenlétek zárolva.</span>
                    <?php } ?>

                    <?php if ($closeMonth->absences_closed != 1) { ?>
                        <button id='absences-close-btn' class="btn btn-danger"
                            >Távollétek zárolása
                        </button>
                    <?php } else { ?>
                        <span class="btn disabled"><i class="fa fa-lock" role="alert"></i> A távollétek zárolva.</span>
                    <?php } ?>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <?= "{$year}. {$monthName}" ?>
        </div>
        <div class="panel-body">

            <?php
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel'  => $userSearch,
                'layout'       => "{items}\n{pager}",
                'columns'      => [
                    'username',
                    'profile.name',
                    'profile.taxnumber',
                    'profile.department.name',
                    [
                        'label'  => 'Szerepkörök',
                        'value'  => function ($data, $id, $index, $dataColumn) {
                            $pieces = array_keys(Yii::$app->authManager->getRolesByUser($data->id));
                            $roles = implode(' ', array_map(function ($data) {
                                return '<span class="label label-default">'.Yii::t('app', $data).'</span>';
                            }, $pieces));
                            return $roles;
                        },
                        'format' => 'raw',
                    ],

                    [
                        'label'     => 'Kitöltötte',
                        'attribute' => 'currentCompletions.id',
                        'value'     => function ($data, $id, $index, $dataColumn) {
                            return count($data->currentCompletions) > 0 ? '<i class="glyphicon
                        glyphicon-ok"></i>' : '';
                        },
                        'format'    => 'raw',
                    ]
                ],
            ]);

            ?>
        </div>
    </div>


<?php
$this->registerJs('
    $(function () {
        console.log("ready!");

        $("#attendances-close-btn").click(function (e) {
            e.preventDefault();
            swal({
                    title             : "Biztos hogy zárol?",
                    text              : "A zárolás után nem lehet a jelenléti adatokon módosítani!",
                    type              : "warning",
                    showCancelButton  : true,
                    confirmButtonColor: "#DD6B5",
                    confirmButtonText : "Igen, zárolok!",
                    closeOnConfirm    : true,
                    closeOnCancel: true
                },
                function () {
                                    window.location.href = "' .
    Url::to(['/attendance/default/close',
        'year'   => $year,
        'month'  => $month,
        'target' => 'attendances'
    ]) . '";

                });
        });
    });
');

$this->registerJs('
    $(function () {
        console.log("ready!");

        $("#absences-close-btn").click(function (e) {
            e.preventDefault();
            swal({
                    title             : "Biztos hogy zárol?",
                    text              : "A zárolás után nem lehet a távolléti adatokon módosítani!",
                    type              : "warning",
                    showCancelButton  : true,
                    confirmButtonColor: "#DD6B5",
                    confirmButtonText : "Igen, zárolok!",
                    closeOnConfirm    : true,
                    closeOnCancel: true
                },
                function () {
                                    window.location.href = "' .
    Url::to(['/attendance/default/close',
        'year'   => $year,
        'month'  => $month,
        'target' => 'absences'
    ]) . '";

                });
        });
    });
');
?>
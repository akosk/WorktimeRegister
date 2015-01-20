<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.28.
 * Time: 12:46
 */

//use kartik\grid\GridView;

use kartik\widgets\AlertBlock;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('attendance', 'Jelenlétek és zárolások');
$this->params['breadcrumbs'][] = $this->title;

?>

    <div class="row">
        <div class="col-md-8">
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

                    <h3><?= $currentUser->profile->department->name ?></h3>
                    <?php if ($hasIncompleteUser) { ?>
                        <div class="alert alert-danger" role="alert"><strong><i class="fa fa-exclamation-triangle"></i>
                                Figyelem!</strong>
                            Van olyan
                            dolgozó aki még nem fejezte be a jelenléti ív kitöltését.
                        </div>
                    <?php } ?>


                    <?php if ($closeMonth->attendances_closed != 1) { ?>
                        <button <?= $canCloseAttendance ?> id='attendances-close-btn' class="btn btn-danger">Jelenlétek
                            zárolása
                        </button>
                    <?php } else { ?>
                        <span class="btn disabled"><i class="fa fa-lock" role="alert"></i> A jelenlétek zárolva.</span>
                    <?php } ?>

                    <?php if ($closeMonth->absences_closed != 1) { ?>
                        <button <?= $canCloseAbsence ?> id='absences-close-btn' class="btn btn-danger"
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
                                return '<span class="label label-default">' . Yii::t('app', $data) . '</span>';
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
                    ],
                    [
                        'class'          => 'yii\grid\ActionColumn',
                        'contentOptions' => [
                            'style' => 'min-width:80px'
                        ],
                        'template'       => '{add-dep-admin} {remove-dep-admin} {index}',
                        'buttons'        => [
                            'add-dep-admin'    => function ($url, $model) {
                                $roles = Yii::$app->authManager->getAssignments($model->id);
                                if (!(Yii::$app->user->can('admin') || Yii::$app->user->can('dep_leader'))) return '';
                                if (is_array($roles) && $roles['dep_admin']) return '';
                                return Html::a('<i class="glyphicon glyphicon-user"></i>', $url, [
                                    'class' => 'btn btn-xs btn-info',
                                    'title' => Yii::t('yii',
                                        'Szervezeti egység adminisztrátor szerepkör hozzáadása'),
                                ]);
                            },
                            'remove-dep-admin' => function ($url, $model) {
                                if (!(Yii::$app->user->can('admin') || Yii::$app->user->can('dep_leader'))) return '';
                                $roles = Yii::$app->authManager->getAssignments($model->id);
                                if (is_array($roles) && !$roles['dep_admin']) return '';
                                return Html::a('<i class="glyphicon glyphicon-user"></i>', $url, [
                                    'class' => 'btn btn-xs btn-danger',
                                    'title' => Yii::t('yii',
                                        'Szervezeti egység adminisztrátor szerepkör eltávolítás'),
                                ]);
                            },
                            'index'            => function ($url, $model) {
                                $roles = Yii::$app->authManager->getAssignments($model->id);
                                $url .= "#/year/" . (app\models\User::$yearFilter) . "/month/" .
                                    (app\models\User::$monthFilter);
                                return Html::a('<i class="glyphicon glyphicon-eye-open"></i>', $url, [
                                    'class' => 'btn btn-xs btn-info',
                                    'title' => Yii::t('yii',
                                        $model->profile->name . ' jelenléti ívének megtekintése'),
                                ]);
                            },
                        ]
                    ],

                ],
            ]);

            ?>
        </div>
        <div class="panel-footer">
            <a class="btn btn-success" href="<?=$holidayReportUrl?>"><i class="fa fa-file-pdf-o"></i> Szabadság
                jelentés</a>
            <a class="btn btn-success" href="<?=$absenceReportUrl?>"><i class="fa fa-file-pdf-o"></i> Távollét
                jelentés</a>
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
                    cancelButtonText  : "Mégsem",
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
                    cancelButtonText  : "Mégsem",
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
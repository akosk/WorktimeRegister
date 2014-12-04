<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.28.
 * Time: 12:46
 */

//use kartik\grid\GridView;

use yii\grid\GridView;
use yii\helpers\Url;

$this->title = Yii::t('attendance', 'Jelenlétek és zárolások');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Fejlesztés alatt!</strong> A menüpont jelenleg fejlesztés alatt van, elnézést az esetleges kellemetlenségekért.
</div>

<div class="row">
    <div class="col-md-8">
        <h2><?= $currentUser->profile->department->name ?></h2>
    </div>
    <div class="col-md-4 text-right">
        <h2>
            <a href="<?= Url::to(['/attendance/default/admin', 'year'  => $prevMonthsYear,
                                                               'month' => $prevMonth]) ?>" class="btn btn-success"><i
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

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                Zárolás
            </div>
            <div class="panel-body">

                <?php if ($hasIncompleteUser) { ?>
                <div class="alert alert-danger" role="alert"><strong><i class="fa fa-exclamation-triangle"></i> A
                        zárolás nem
                        javasolt!</strong>
                    Van olyan
                    dolgozó aki még nem fejezte be a jelenléti ív kitöltését.
                </div>
                <?php } ?>

                <button class="btn btn-danger">Jelenlétek zárolása</button>
                <button class="btn btn-danger">Távollétek zárolása</button>

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
                [
                    'label'=>'Kitöltötte',
                    'attribute'=>'currentCompletions.id',
                    'value'  => function ($data, $id, $index, $dataColumn) {
                        return count($data->currentCompletions) > 0 ? '<i class="glyphicon
                        glyphicon-ok"></i>' : '';
                    },
                    'format' => 'raw',
                ]
            ],
        ]);

        ?>
    </div>
</div>
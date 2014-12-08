<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.12.03.
 * Time: 14:53
 */
use kartik\widgets\ActiveForm;
use kartik\widgets\AlertBlock;
use kartik\widgets\FileInput;
use yii\grid\GridView;

?>

<div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Fejlesztés alatt!</strong> A menüpont jelenleg fejlesztés alatt van, elnézést az esetleges
    kellemetlenségekért.
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        Felhasználók importálása (XLS)
    </div>
    <div class="panel-body">
        <?php

        $form1 = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'] // important
        ]);

        echo FileInput::widget([
            'name'          => 'attachment',
            'pluginOptions' => [
                'removeLabel'   => 'Eltávolítás',
                'browseLabel'   => 'Tallózás',
                'uploadLabel'   => 'Feltöltés',
                'elCaptionText' => '#customCaption'
            ]
        ]);

        ActiveForm::end();
        ?>
    </div>
</div>


<?php
echo AlertBlock::widget([
    'useSessionFlash' => true,
    'delay'           => false,
    'type'            => AlertBlock::TYPE_ALERT
]);
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Felhasználók adatai a legutóbbi import szerint
    </div>
    <div class="panel-body">

        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $userImportSearch,
            'layout'       => "{items}\n{pager}",
            'columns'      => [
                'taxnumber',
                'relationship',
                'num',
                'name_prefix',
                'name',
                'reference_number',
                'department_code',
                'department_name',
                'group',
                'admin',
            ],
        ]);

        ?>
    </div>
</div>
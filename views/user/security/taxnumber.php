<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.17.
 * Time: 12:58
 */

use kartik\widgets\AlertBlock;

echo AlertBlock::widget([
    'useSessionFlash' => true,
    'type' => AlertBlock::TYPE_ALERT
]);
?>


<?php if ($model->taxnumber=='') { ?>
<div class="alert alert-danger" role="alert"><strong>Úgy tűnik, még nem adta meg az adószámát!</strong> A
    rendszer
    használatának feltétele az adószám megadása. A továbblépéshez kérem adja meg az adószámát.</div>
<?php } ?>

<div class="row">
    <div class="col-md-offset-4 col-md-4">


        <div class="panel panel-default">
            <div class="panel-heading">
                Adószám megadása
            </div>
            <div class="panel-body">
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'id'                     => 'profile-form',
                    'options'                => ['class' => 'form-horizontal'],
                    'fieldConfig'            => [
                        'template'     => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-9\">{error}\n{hint}</div>",
                        'labelOptions' => ['class' => 'col-lg-3 control-label'],
                    ],
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false
                ]); ?>

                <?= $form->field($model, 'taxnumber') ?>


                <div class="form-group">
                    <div class="col-md-12">
                        <?= \yii\helpers\Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-success']) ?>
                        <br>
                    </div>
                </div>

                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
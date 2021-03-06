<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.12.17.
 * Time: 6:02
 */


use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var dektrium\user\models\UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php echo $this->render('@vendor/dektrium/yii2-user/views/admin/flash') ?>

<?php echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'layout'       => "{items}\n{pager}",
    'columns'      => [
        'username',
        [
            'attribute' => 'name',
            'value'     => function ($model, $key, $index, $widget) {
                return $model->profile->name;
            }
        ],
        'email:email',
        [
            'attribute' => 'registration_ip',
            'value'     => function ($model, $key, $index, $widget) {
                return $model->registration_ip == null ? '<span class="not-set">' . Yii::t('user', '(not set)') . '</span>' : long2ip($model->registration_ip);
            },
            'format'    => 'html',
        ],
        [
            'attribute' => 'created_at',
            'value'     => function ($model, $key, $index, $widget) {
                return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
            }
        ],
        [
            'header'  => Yii::t('user', 'Confirmation'),
            'value'   => function ($model, $key, $index, $widget) {
                if ($model->isConfirmed) {
                    return '<div class="text-center"><span class="text-success">' . Yii::t('user', 'Confirmed') . '</span></div>';
                } else {
                    return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                        'class'        => 'btn btn-xs btn-success btn-block',
                        'data-method'  => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure to confirm this user?'),
                    ]);
                }
            },
            'format'  => 'raw',
            'visible' => Yii::$app->getModule('user')->enableConfirmation
        ],
        [
            'header' => Yii::t('user', 'Block status'),
            'value'  => function ($model, $key, $index, $widget) {
                if ($model->isBlocked) {
                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                        'class'        => 'btn btn-xs btn-success btn-block',
                        'data-method'  => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure to unblock this user?')
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                        'class'        => 'btn btn-xs btn-danger btn-block',
                        'data-method'  => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure to block this user?')
                    ]);
                }
            },
            'format' => 'raw',
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{update} {profile}',
            'contentOptions'=>['style'=>'min-width: 69px;'],
            'buttons'  => [
                'update'  => function ($url, $model) {
                    return Html::a('<i class="glyphicon glyphicon-wrench"></i>', $url, [
                        'class' => 'btn btn-xs btn-info',
                        'title' => Yii::t('yii', 'Update'),
                    ]);
                },
                'profile' => function ($url, $model) {
                    $url=\yii\helpers\Url::to(['/user/settings/profile','id'=>$model->id]);
                    return Html::a('<i class="glyphicon glyphicon-pencil"></i>', $url, [
                        'class' => 'btn btn-xs btn-info',
                        'title' => Yii::t('yii', 'Profil szerkesztése'),
                    ]);
                },
                'delete'  => function ($url, $model) {
                    return Html::a('<i class="glyphicon glyphicon-trash"></i>', $url, [
                        'class'        => 'btn btn-xs btn-danger',
                        'data-method'  => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure to delete this user?'),
                        'title'        => Yii::t('yii', 'Delete'),
                    ]);
                },
            ]
        ],
    ],
]); ?>

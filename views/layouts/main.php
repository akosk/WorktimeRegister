<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>


    <!--[if lt IE 9 ]>
    <script>
        alert('Az Ön böngészője elavult! Az OK gomb megnyomása után válasszon ki egyet az ingyenesen használható ' +
        'modern böngészők közül.');
        window.location = "http://browsehappy.com/?locale=hu";
    </script>
    <![endif]-->

</head>
<body>

<?php $this->beginBody() ?>
<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '<i class="fa fa-clock-o"></i> Munkaidő nyilvántartó',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => [
            'class' => 'navbar-default  navbar-fixed-top navbar-inverse cbp-af-header',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items'   => [
            [
                'label'   => 'Adószám',
                'url'     => ['//user/security/taxnumber'],
                'visible' => !Yii::$app->user->isGuest
            ],

            [
                'label'   => 'Jelenléti ív',
                'url'     => ['//attendance/default/index'],
                'visible' => !Yii::$app->user->isGuest
            ],

            [
                'label'   => 'Adminisztráció',
                'visible' =>    Yii::$app->user->can('admin') ||
                                Yii::$app->user->can('dep_admin') ||
                                Yii::$app->user->can('dep_leader') ||
                                Yii::$app->user->can('payroll_manager'),
                'items'   => [

                    [
                        'label'   => 'Felhasználókezelés',
                        'url'     => ['//user/admin/index'],
                        'visible' => Yii::$app->user->can('admin'),
                    ],

                    [
                        'label'   => 'Jelenlétek és zárolás',
                        'url'     => ['//attendance/default/admin'],
//                        'visible' => !Yii::$app->user->isGuest
                    ],

                    [
                        'label'   => 'Dolgozók adatainak importálása',
                        'url'     => ['//attendance/default/import'],
                        'visible' => Yii::$app->user->can('admin'),
                    ],

//                    [
//                        'label'   => 'Szervezeti egységek',
////                        'url'     => ['//attendance/default/unions'],
//                        'visible' => Yii::$app->user->can('admin'),
//                    ],

                ]
            ],

            Yii::$app->user->isGuest ?
                ['label' => 'Bejelentkezés', 'url' => ['//user/security/login']] :
                ['label'       => 'Kijelentkezés (' . Yii::$app->user->identity->username . ')',
                 'url'         => ['//user/security/logout'],
                 'linkOptions' => ['data-method' => 'post']],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left"><a href="http://iszk.uni-miskolc.hu/">Copyright &copy; <?= date('Y') ?> Miskolci Egyetem
                Informatikai Szolgáltató
                Központ</a></p>

    </div>
</footer>

<?php $this->endBody() ?>

<script src="<?=Url::base();?>/js/classie.js" type="text/javascript"></script>
<script src="<?=Url::base();?>/js/cbpAnimatedHeader.min.js" type="text/javascript"></script>
</body>
</html>
<?php $this->endPage() ?>



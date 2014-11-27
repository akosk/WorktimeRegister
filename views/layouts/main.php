<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
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
</head>
<body>

<?php $this->beginBody() ?>
<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Munkaidő nyilvántartó',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => [
            'class' => 'navbar-inverse  navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items'   => [
//            [
//                'label'       => '<i class="glyphicon glyphicon-home"></i>',
//                'encode' => false,
//                'url'         => ['/site/index'],
//            ],
            [
                'label'   => 'Felhasználókezelés',
                'visible' => !Yii::$app->user->isGuest,
                'items'   => [
                    [
                        'label'   => 'Felhasználókezelés',
                        'url'     => ['//user/admin/index'],
                        'visible' => !Yii::$app->user->isGuest,
                    ],
                    [
                        'label'   => 'Saját profil',
                        'url'     => ['//user/profile/index'],
                        'visible' => !Yii::$app->user->isGuest
                    ],
                    [
                        'label'   => 'Saját profil szerkesztése',
                        'url'     => ['//user/settings/profile'],
                        'visible' => !Yii::$app->user->isGuest
                    ],
                    [
                        'label'   => 'Adószám',
                        'url'     => ['//user/security/taxnumber'],
                        'visible' => !Yii::$app->user->isGuest
                    ],

                ]
            ],

            [
                'label'   => 'Jelenlét',
                'visible' => !Yii::$app->user->isGuest,
                'items'   => [
                    [
                        'label'   => 'Jelenléti ív',
                        'url'     => ['//attendance/default/index'],
                        'visible' => !Yii::$app->user->isGuest
                    ],

                    [
                        'label'   => 'Szervezeti egységek',
//                        'url'     => ['//attendance/default/unions'],
                        'visible' => !Yii::$app->user->isGuest
                    ],
                    [
                        'label'   => 'Saját beállítások',
//                        'url'     => ['//attendance/default/profile'],
                        'visible' => !Yii::$app->user->isGuest
                    ],

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
        <p class="pull-left"><a href="http://iszk.uni-miskolc.hu/">Copyright &copy; <?= date('Y') ?> Miskolci Egyetem Informatikai Szolgáltató
                Központ</a></p>

    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.17.
 * Time: 12:00
 */

namespace app\controllers;

use Yii;
use yii\base\InlineAction;
use app\models\User;
use dektrium\user\controllers\SecurityController as BaseSecurityController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class SecurityController extends BaseSecurityController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['login', 'auth'],
                        'roles'   => ['?']
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['logout', 'taxnumber'],
                        'roles'   => ['@']
                    ],
                ]
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ];
    }


    public function actionLogin()
    {
        $model = $this->module->manager->createLoginForm();

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {

            $user = User::find()->where('username=:username', [':username' => $model->login])->one();
            if ($user) {
                if ($user->profile->taxnumber == '') {
                    $taxnumberUrl = \Yii::$app->urlManager->createAbsoluteUrl(['/user/security/taxnumber']);
                    return Yii::$app->getResponse()->redirect($taxnumberUrl);
                }
                return Yii::$app->getResponse()->redirect(Url::toRoute('/attendance/default/index'));
            }


        }

        return $this->render('login', [
            'model' => $model
        ]);
    }

    public function actionTaxnumber()
    {
        $user = User::findOne(Yii::$app->getUser()->id);
        $model = $user->profile;

        if (isset($_POST['Profile'])) {
            $taxnumber = $_POST['Profile']['taxnumber'];
            if ($taxnumber != '') {
                $taxnumberWasEmpty = $model->taxnumber == '';
                $model->taxnumber = $taxnumber;
                $model->save();
                if ($taxnumberWasEmpty) {
                    Yii::$app->getResponse()->redirect(Url::toRoute('/attendance/default/index'));
                } else {
                    Yii::$app->getSession()->setFlash('success', '<strong>Mentve!</strong> Az adószám mentése megtörtént.');
                }
            } else {
                $model->addError('taxnumber', 'Az adószám kitöltése kötelező');
            }

        }

        return $this->render('taxnumber', [
            'model' => $model,
            'user'  => $user
        ]);

    }

}
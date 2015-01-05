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
use dektrium\user\controllers\AdminController as BaseAdminController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class AdminController extends BaseAdminController
{

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete'  => ['post'],
                    'confirm' => ['post'],
                    'block'   => ['post']
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'block', 'confirm'],
                        'allow'   => true,
                        'roles'   => ['admin'],
                    ],
                ]
            ]
        ];
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('user.success', \Yii::t('user', 'User has been updated'));
            return $this->refresh();
        }


        $roles = \Yii::$app->authManager->getRoles();
        $translatedRoles = [];
        foreach ($roles as $role) {
            $trans=Yii::t('app',$role->name);
            $translatedRoles[$trans]=$trans;
        }


        return $this->render('update', [
            'model'           => $model,
            'translatedRoles' => $translatedRoles
        ]);
    }


}
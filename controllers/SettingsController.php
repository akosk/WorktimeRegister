<?php
/**
 * Created: Ákos Kiszely
 * Date: 2015.01.26.
 * Time: 15:21
 */
namespace app\controllers;

use Yii;
use dektrium\user\controllers\SettingsController as BaseSettingsController;

use yii\authclient\ClientInterface;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SettingsController extends BaseSettingsController
{

    public function actionProfile()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : \Yii::$app->user->identity->getId();
        $model = $this->module->manager->findProfileById($id);

        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Profile settings have been successfully saved'));
            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model
        ]);
    }


}
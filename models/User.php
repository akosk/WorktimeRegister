<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.06.
 * Time: 10:25
 */

namespace app\models;

use dektrium\user\models\User as BaseUser;

class User extends BaseUser
{

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($_POST['roles']) && \Yii::$app->user->can('admin')) {
            $this->assignNewRoles($_POST['roles']);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function assignNewRoles($newRoles)
    {
        $auth = \Yii::$app->authManager;
        $auth->revokeAll($this->id);
        foreach ($newRoles as $role) {
            $authRole = $auth->getRole($role);
            if ($authRole) {
                $auth->assign($authRole, $this->id);
            }
        }
    }

}
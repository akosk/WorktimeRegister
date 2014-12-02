<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.06.
 * Time: 10:25
 */

namespace app\models;

use app\modules\attendance\models\Completion;
use dektrium\user\models\User as BaseUser;

class User extends BaseUser
{

    public static $yearFilter, $monthFilter;

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

    public function getCompletions()
    {
        return $this->hasMany(Completion::className(), ['user_id' => 'id']);
    }

    public function getCurrentCompletions()
    {
        return $this->hasMany(Completion::className(), ['user_id' => 'id'])
            ->onCondition('year=:year AND month=:month', [':year' => self::$yearFilter, ':month'=>self::$monthFilter]);
    }

}
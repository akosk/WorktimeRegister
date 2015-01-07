<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.06.
 * Time: 10:25
 */

namespace app\models;

use app\modules\attendance\models\Completion;
use dektrium\user\models\User as BaseUser;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Role;

class User extends BaseUser
{

    public static $yearFilter, $monthFilter;


    public function rules()
    {
        return [
            // username rules
            ['username', 'required', 'on' => ['register', 'connect', 'create', 'update']],
            ['username', 'match', 'pattern' => '/^[a-zA-Z]\w+$/'],
            ['username', 'string', 'min' => 3, 'max' => 25],
            ['username', 'unique'],
            ['username', 'trim'],

            // email rules
            ['email', 'required', 'on' => ['register', 'connect', 'create', 'update', 'update_email']],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique'],
            ['email', 'trim'],

            // unconfirmed email rules
            ['unconfirmed_email', 'required', 'on' => 'update_email'],
            ['unconfirmed_email', 'unique', 'targetAttribute' => 'email', 'on' => 'update_email'],
            ['unconfirmed_email', 'email', 'on' => 'update_email'],

            // password rules
            ['password', 'required', 'on' => ['register', 'update_password']],

            // current password rules
            ['current_password', 'required', 'on' => ['update_email', 'update_password']],
            ['current_password', function ($attr) {
                if (!empty($this->$attr) && !Password::validate($this->$attr, $this->password_hash)) {
                    $this->addError($attr, \Yii::t('user', 'Current password is not valid'));
                }
            }, 'on' => ['update_email', 'update_password']],
        ];
    }

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
        foreach ($newRoles as $untranslatedRoleName) {
            $role=$this->translate($untranslatedRoleName);
            $authRole = $auth->getRole($role);
            if ($authRole) {
                $auth->assign($authRole, $this->id);
            }
        }
    }

    private function translate($s) {
        $arr=ArrayHelper::map(
            \Yii::$app->authManager->getRoles(),
            function ($role){
                return Yii::t('app', $role->name);
            },
            'name'
        );
        return $arr[$s];
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

    public function updateUserDepartmentIdAndName($user_id = null)
    {
        $where='';
        if ($user_id!==null) {
            $where="WHERE profile.user_id=$user_id";
        }
        $q = "UPDATE profile
                LEFT JOIN user_import ui ON ui.taxnumber=profile.taxnumber
                LEFT JOIN department d ON ui.department_code=d.code
                SET profile.name=ui.name, department_id=d.id $where";
        return Yii::$app->db->createCommand($q)->execute();

    }

    public function updateUserRoles($user_id=null)
    {
        $where='';
        if ($user_id!==null) {
            $where="WHERE p.user_id=$user_id";
        }

        $q = "SELECT p.user_id, t.`group`, t.admin
FROM user_import t
INNER JOIN profile p ON p.taxnumber=t.taxnumber $where";


        $res = Yii::$app->db->createCommand($q)->queryAll();
        $auth = \Yii::$app->authManager;
        $instructor = $auth->getRole('instructor');
        $dep_leader = $auth->getRole('dep_leader');
        $worker = $auth->getRole('worker');

        for ($i = 0; $i < count($res); $i++) {
            if (strtolower(substr($res[$i]['group'], 0, 3)) == "nem") {
                $auth->revoke($instructor, $res[$i]['user_id']);
                $this->assignRole($worker, $res[$i]['user_id']);
            } else {
                $this->assignRole($instructor, $res[$i]['user_id']);
                $auth->revoke($worker, $res[$i]['user_id']);
            }

            if ($res[$i]['admin'] == 1) {
                $this->assignRole($dep_leader, $res[$i]['user_id']);
            } else {
                $auth->revoke($dep_leader, $res[$i]['user_id']);
            }
        }

    }

    protected function assignRole(Role $role, $user_id)
    {
        $assingment = \Yii::$app->authManager->getAssignment($role->name, $user_id);
        if (!$assingment) {
            \Yii::$app->authManager->assign($role, $user_id);
        }

    }

}
<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.06.
 * Time: 10:25
 */

namespace app\models;

use app\components\LdapManager;
use dektrium\user\helpers\Password;
use dektrium\user\models\LoginForm as BaseLoginForm;

class LoginForm extends BaseLoginForm
{

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['login', 'trim'],
//            ['login', function ($attribute) {
//                if ($this->user !== null) {
//                    $confirmationRequired = $this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin;
//                    if ($confirmationRequired && !$this->user->isConfirmed) {
//                        $this->addError($attribute, \Yii::t('user', 'You need to confirm your email address'));
//                    }
//                    if ($this->user->getIsBlocked()) {
//                        $this->addError($attribute, \Yii::t('user', 'Your account has been blocked'));
//                    }
//                }
//            }],
            ['rememberMe', 'boolean'],
        ];
    }


    public function login()
    {
        if ($this->validate()) {
            return \Yii::$app->getUser()->login($this->user, $this->rememberMe ? $this->module->rememberFor : 0);
        } else {
            return false;
        }

    }


    /** @inheritdoc */
    public function beforeValidate()
    {
        $ldapManager = new LdapManager();

        $isAuthenticated = $ldapManager->authenticate($this->login, $this->password);
        if (!$isAuthenticated) {
            $this->addError('password', \Yii::t('user', 'Invalid login or password'));
            return false;
        };

        $this->createUserIfNotExists($ldapManager);

        return true;
    }

    public function createUserIfNotExists(LdapManager $ldapManager)
    {
        $this->user = $this->module->manager->findUserByUsernameOrEmail($this->login);
        if (!$this->user) {
            $entry = $ldapManager->getEntryByUID($this->login);

            $user = new User();
            $user->scenario = 'create';
            $user->username = $this->login;
            $user->password = $this->password;
            $user->email = $entry['mail'][0];



            if ($user->create()) {
                $this->user = $user;
                $profile = $user->profile;
                $profile->name=$entry['sn'][0].' '.$entry['givenname'][0];
                $profile->public_email=$user->email;
                if (!$profile->save(false)) {

                    $this->addError('login', \Yii::t('user', 'A felhasználó profil létrehozása sikertelen'));
                }
            } else {
                $this->addError('login', \Yii::t('user', 'A felhasználó létrehozása sikertelen'));
            }
        }

    }

}
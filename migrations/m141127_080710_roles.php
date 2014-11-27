<?php

use yii\db\Schema;
use yii\db\Migration;

class m141127_080710_roles extends Migration
{
    public function up()
    {
        $auth=\Yii::$app->authManager;
        $editor = $auth->getRole('editor');
        $auth->remove($editor);

        $dep_leader = $auth->createRole('dep_leader');
        $auth->add($dep_leader);

        $dep_admin = $auth->createRole('dep_admin');
        $auth->add($dep_admin);

        $payroll_manager = $auth->createRole('payroll_manager');
        $auth->add($payroll_manager);

        $worker = $auth->createRole('worker');
        $auth->add($worker);

        $instructor = $auth->createRole('instructor');
        $auth->add($instructor);
    }

    public function down()
    {
        echo "m141127_080710_roles cannot be reverted.\n";

        return false;
    }
}

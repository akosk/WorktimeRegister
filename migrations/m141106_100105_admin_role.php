<?php

use yii\db\Schema;
use yii\db\Migration;

class m141106_100105_admin_role extends Migration
{
    public function up()
    {
        $auth=\Yii::$app->authManager;
        $admin = $auth->createRole('admin');
        $auth->add($admin);
    }

    public function down()
    {
        echo "m141106_100105_admin_role cannot be reverted.\n";

        return false;
    }
}

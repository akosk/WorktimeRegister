<?php

use yii\db\Schema;
use yii\db\Migration;

class m141106_133619_editor_role extends Migration
{
    public function up()
    {
        $auth=\Yii::$app->authManager;
        $editor = $auth->createRole('editor');
        $auth->add($editor);
    }

    public function down()
    {
        echo "m141106_133619_editor_role cannot be reverted.\n";

        return false;
    }
}

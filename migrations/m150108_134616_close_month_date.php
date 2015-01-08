<?php

use yii\db\Schema;
use yii\db\Migration;

class m150108_134616_close_month_date extends Migration
{
    public function up()
    {
        $q="ALTER TABLE `close_month`
ADD `absences_close_time` datetime NULL,
COMMENT='';";
        $this->execute($q);
        $q="ALTER TABLE `close_month`
ADD `attendances_close_time` datetime NULL,
COMMENT='';";
        $this->execute($q);

    }

    public function down()
    {
        echo "m150108_134616_close_month_date cannot be reverted.\n";

        return false;
    }
}

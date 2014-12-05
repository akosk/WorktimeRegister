<?php

use yii\db\Schema;
use yii\db\Migration;

class m141205_080816_close_month extends Migration
{
    public function up()
    {
        $q = "
CREATE TABLE `close_month` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `department_id` int NOT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `attendances_closed` int NULL,
  `absences_closed` int NULL
) COMMENT='' ENGINE='InnoDB';";
        $this->execute($q);
    }

    public function down()
    {
        echo "m141205_080816_close_month cannot be reverted.\n";

        return false;
    }
}

<?php

use yii\db\Schema;
use yii\db\Migration;

class m150212_134230_customworkday extends Migration
{
    public function up()
    {
        $q = "CREATE TABLE `custom_workday` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `create_time` datetime NOT NULL,
  `create_user` int NOT NULL
) COMMENT='';
(0.045 s)";


        $q="ALTER TABLE `custom_workday`
ADD `type` enum('WORKING_DAY','HOLIDAY') NOT NULL,
COMMENT='';";
        $this->execute($q);
    }

    public function down()
    {
        echo "m150212_134230_customworkday cannot be reverted.\n";

        return false;
    }
}

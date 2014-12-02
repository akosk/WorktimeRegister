<?php

use yii\db\Schema;
use yii\db\Migration;

class m141201_131720_department extends Migration
{
    public function up()
    {
        $q = "CREATE TABLE `department` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL
) COMMENT='' ENGINE='InnoDB';";

        $this->execute($q);

        $q="ALTER TABLE `profile`
ADD `department_id` int(11) NULL,
ADD FOREIGN KEY (`department_id`) REFERENCES `department` (`id`),
COMMENT='';";

        $this->execute($q);
    }

    public function down()
    {
        echo "m141201_131720_department cannot be reverted.\n";

        return false;
    }
}

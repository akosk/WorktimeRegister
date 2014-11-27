<?php

use yii\db\Schema;
use yii\db\Migration;

class m141126_075137_red_letter_day extends Migration
{
    public function up()
    {
        $q = "CREATE TABLE `red_letter_day` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `date` date NOT NULL COMMENT 'Dátum',
  `type` enum('WORKING_DAY','HOLIDAY') NOT NULL COMMENT 'Ünnepnap'
) COMMENT='' ENGINE='InnoDB';";

        $this->execute($q);

        $q="CREATE TABLE `absence` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` varchar(10) NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `create_time` datetime NOT NULL,
  `create_user` int NOT NULL
) COMMENT='' ENGINE='InnoDB';";

        $this->execute($q);

        $q="ALTER TABLE `absence`
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE RESTRICT";

        $this->execute($q);

    }

    public function down()
    {
        echo "m141126_075137_red_letter_day cannot be reverted.\n";

        return false;
    }
}

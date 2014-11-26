<?php

use yii\db\Schema;
use yii\db\Migration;

class m141107_123231_attendance extends Migration
{
    public function up()
    {
        $q="CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Azonosító',
  `date` date NOT NULL COMMENT 'Dátum',
  `start` datetime DEFAULT NULL COMMENT 'Munkaidő kezdete',
  `end` datetime DEFAULT NULL COMMENT 'Munkaidő vége',
  `user_id` int(11) NOT NULL COMMENT 'Felhasználó',
  `create_time` datetime NOT NULL COMMENT 'Létrehozás ideje',
  `create_user_id` int(11) NOT NULL COMMENT 'Létrehozta',
  `update_time` datetime DEFAULT NULL COMMENT 'Módosítás ideje',
  `update_user_id` int(11) DEFAULT NULL COMMENT 'Módosította',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `create_user_id` (`create_user_id`),
  KEY `update_user_id` (`update_user_id`),
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;";
        \Yii::$app->db->createCommand($q)->execute();

    }

    public function down()
    {
        echo "m141107_123231_attendance cannot be reverted.\n";

        return false;
    }
}

<?php

use yii\db\Schema;
use yii\db\Migration;

class m141208_084251_user_import extends Migration
{
    public function up()
    {
        $q="CREATE TABLE `user_import` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `taxnumber` varchar(255) NOT NULL COMMENT 'Adószám',
  `name_prefix` varchar(255) NOT NULL COMMENT 'Előnév',
  `name` varchar(255) NOT NULL COMMENT 'Név',
  `reference_number` varchar(255) NOT NULL COMMENT 'Törzsszám',
  `department_code` varchar(255) NOT NULL COMMENT 'Szervezeti egység kód',
  `department_name` varchar(255) NOT NULL COMMENT 'Szervezeti egység név',
  `group` varchar(255) NOT NULL COMMENT 'Állomány - csoport',
  `admin` int NOT NULL COMMENT 'Vezető'
) COMMENT='' ENGINE='InnoDB';";
        $this->execute($q);
        $q="ALTER TABLE `user_import`
ADD `relationship` varchar(255) COLLATE 'utf8_hungarian_ci' NOT NULL COMMENT 'Jogviszony' AFTER `taxnumber`,
ADD `num` varchar(255) COLLATE 'utf8_hungarian_ci' NOT NULL COMMENT 'Sorszám' AFTER `relationship`,
COMMENT='';";
        $this->execute($q);

        $q="ALTER TABLE `user_import`
CHANGE `taxnumber` `taxnumber` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Adószám' AFTER `id`,
CHANGE `relationship` `relationship` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Jogviszony' AFTER `taxnumber`,
CHANGE `num` `num` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Sorszám' AFTER `relationship`,
CHANGE `name_prefix` `name_prefix` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Előnév' AFTER `num`,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Név' AFTER `name_prefix`,
CHANGE `reference_number` `reference_number` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Törzsszám' AFTER `name`,
CHANGE `department_code` `department_code` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Szervezeti egység kód' AFTER `reference_number`,
CHANGE `department_name` `department_name` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Szervezeti egység név' AFTER `department_code`,
CHANGE `group` `group` varchar(255) COLLATE 'utf8_hungarian_ci' NULL COMMENT 'Állomány - csoport' AFTER `department_name`,
CHANGE `admin` `admin` int(11) NULL COMMENT 'Vezető' AFTER `group`,
COMMENT='';";
        $this->execute($q);

        $q="ALTER TABLE `profile`
CHANGE `name` `name` varchar(255) COLLATE 'utf8_hungarian_ci' NULL AFTER `user_id`,
CHANGE `public_email` `public_email` varchar(255) COLLATE 'utf8_hungarian_ci' NULL AFTER `name`,
CHANGE `gravatar_email` `gravatar_email` varchar(255) COLLATE 'utf8_hungarian_ci' NULL AFTER `public_email`,
CHANGE `gravatar_id` `gravatar_id` varchar(32) COLLATE 'utf8_hungarian_ci' NULL AFTER `gravatar_email`,
CHANGE `location` `location` varchar(255) COLLATE 'utf8_hungarian_ci' NULL AFTER `gravatar_id`,
CHANGE `website` `website` varchar(255) COLLATE 'utf8_hungarian_ci' NULL AFTER `location`,
CHANGE `bio` `bio` text COLLATE 'utf8_hungarian_ci' NULL AFTER `website`,
CHANGE `taxnumber` `taxnumber` varchar(250) COLLATE 'utf8_hungarian_ci' NULL AFTER `bio`,
COMMENT='' COLLATE 'utf8_hungarian_ci';";
        $this->execute();
    }

    public function down()
    {
        echo "m141208_084251_user_import cannot be reverted.\n";

        return false;
    }
}

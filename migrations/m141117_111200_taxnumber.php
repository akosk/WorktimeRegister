<?php

use yii\db\Schema;
use yii\db\Migration;

class m141117_111200_taxnumber extends Migration
{
    public function up()
    {
        $this->addColumn('profile','taxnumber','varchar(250)');
    }

    public function down()
    {

        return false;
    }
}

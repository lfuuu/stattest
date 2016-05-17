<?php

use app\models\voip\Registry;

class m160517_103053_voip_registry_comment extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(Registry::tableName(), 'comment', $this->string(1024)->notNull()->defaultValue(''));
    }

    public function down()
    {
        $this->dropColumn(Registry::tableName(), 'comment');
    }
}
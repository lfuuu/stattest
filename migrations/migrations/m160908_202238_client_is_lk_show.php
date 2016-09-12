<?php

use app\models\ClientSuper;

class m160908_202238_client_is_lk_show extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(ClientSuper::tableName(), 'is_lk_exists', $this->integer(1)->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn(ClientSuper::tableName(), 'is_lk_exists');
    }
}
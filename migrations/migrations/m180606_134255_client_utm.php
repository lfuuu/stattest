<?php

use app\models\ClientSuper;

/**
 * Class m180606_134255_client_utm
 */
class m180606_134255_client_utm extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientSuper::tableName(), 'utm', $this->text());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientSuper::tableName(), 'utm');
    }
}

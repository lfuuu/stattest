<?php

use app\modules\uu\models\AccountLogMin;

/**
 * Class m180903_191430_delete_mgp_resource
 */
class m180903_191430_delete_mgp_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountLogMin::tableName();
        $this->dropColumn($tableName, 'price_with_coefficient');
        $this->dropColumn($tableName, 'price_resource');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountLogMin::tableName();
        $this->addColumn($tableName, 'price_with_coefficient', $this->float());
        $this->addColumn($tableName, 'price_resource', $this->float());
    }
}

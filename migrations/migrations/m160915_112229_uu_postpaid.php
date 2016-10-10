<?php

use app\models\ClientAccount;

class m160915_112229_uu_postpaid extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientAccount::tableName();
        $this->addColumn($tableName, 'is_postpaid', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $tableName = ClientAccount::tableName();
        $this->dropColumn($tableName, 'is_postpaid');
    }
}
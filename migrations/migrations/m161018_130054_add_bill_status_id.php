<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Bill;

class m161018_130054_add_bill_status_id extends \app\classes\Migration
{
    private $fieldName = 'is_converted';

    public function up()
    {
        $tableName = Bill::tableName();
        $this->addColumn($tableName, $this->fieldName, $this->integer(1)->notNull()->defaultValue(0));
        $this->createIndex('idx-' . $tableName . '-' . $this->fieldName, $tableName, $this->fieldName);

        // сбросить привязку проводок к счетам для того, чтобы посчитать заново по-другому (абонентку вперед, все остальное назад)
        AccountEntry::updateAll(['bill_id' => null]);
    }

    public function down()
    {
        $tableName = Bill::tableName();
        $this->dropColumn($tableName, $this->fieldName);
    }
}
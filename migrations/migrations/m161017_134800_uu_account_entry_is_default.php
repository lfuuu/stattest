<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Bill;

class m161017_134800_uu_account_entry_is_default extends \app\classes\Migration
{
    private $field = 'is_default';

    public function up()
    {
        $tableName = AccountEntry::tableName();
        $this->addColumn($tableName, $this->field, $this->integer()->notNull()->defaultValue(1));
        $this->dropIndex('uniq-' . $tableName . '-' . 'date-type_id-account_tariff_id', $tableName);
        $this->createIndex('uniq-' . $tableName . '-' . 'date-type_id-account_tariff_id', $tableName, ['date', 'type_id', 'account_tariff_id', $this->field], true);

        $tableName = Bill::tableName();
        $this->addColumn($tableName, $this->field, $this->integer()->notNull()->defaultValue(1));
        $this->dropIndex('uniq-' . $tableName . '-' . 'date-client_account_id', $tableName);
        $this->createIndex('uniq-' . $tableName . '-' . 'date-client_account_id', $tableName, ['date', 'client_account_id', $this->field], true);

    }

    public function down()
    {
        $tableName = AccountEntry::tableName();
        $this->dropIndex('uniq-' . $tableName . '-' . 'date-type_id-account_tariff_id', $tableName);
        $this->createIndex('uniq-' . $tableName . '-' . 'date-type_id-account_tariff_id', $tableName, ['date', 'type_id', 'account_tariff_id'], true);
        $this->dropColumn($tableName, $this->field);

        $tableName = Bill::tableName();
        $this->dropIndex('uniq-' . $tableName . '-' . 'date-client_account_id', $tableName);
        $this->createIndex('uniq-' . $tableName . '-' . 'date-client_account_id', $tableName, ['date', 'client_account_id'], true);
        $this->dropColumn($tableName, $this->field);
    }
}
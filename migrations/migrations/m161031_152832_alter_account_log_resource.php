<?php

use app\classes\uu\model\AccountLogResource;

class m161031_152832_alter_account_log_resource extends \app\classes\Migration
{
    public function up()
    {
        $tableName = AccountLogResource::tableName();
        $this->alterColumn($tableName, 'amount_overhead', $this->float());
        $this->update($tableName, ['amount_overhead' => new \yii\db\Expression('GREATEST(0, amount_use - amount_free)')]);
    }

    public function down()
    {
        $tableName = AccountLogResource::tableName();
        $this->alterColumn($tableName, 'amount_overhead', $this->integer());
    }
}
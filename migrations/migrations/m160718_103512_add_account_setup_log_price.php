<?php

use app\classes\uu\model\AccountLogSetup;
use yii\db\Expression;

class m160718_103512_add_account_setup_log_price extends \app\classes\Migration
{
    public function up()
    {
        $tableName = AccountLogSetup::tableName();
        $this->addColumn($tableName, 'price_setup', $this->float()->notNull()->defaultValue(0));
        $this->addColumn($tableName, 'price_number', $this->float()->notNull()->defaultValue(0));

        AccountLogSetup::updateAll(['price_setup' => new Expression('price')]);
    }

    public function down()
    {
        $tableName = AccountLogSetup::tableName();
        $this->dropColumn($tableName, 'price_setup');
        $this->dropColumn($tableName, 'price_number');
    }
}
<?php
use app\models\ClientAccount;

/**
 * Handles the dropping column `is_calc_with_tax` for table `clients`.
 */
class m170224_093059_drop_column_clients_is_calc_with_tax extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn(ClientAccount::tableName(), 'is_calc_with_tax');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn(ClientAccount::tableName(), 'is_calc_with_tax', $this->integer()->defaultValue(null));
    }
}

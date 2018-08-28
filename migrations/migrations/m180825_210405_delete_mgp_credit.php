<?php

use app\models\ClientAccount;

/**
 * Class m180825_210405_delete_mgp_credit
 */
class m180825_210405_delete_mgp_credit extends \app\classes\Migration
{
	/**
	 * Up
	 */
	public function safeUp()
	{
		$this->dropColumn(ClientAccount::tableName(), 'credit_mgp');
	}

	/**
	 * Down
	 */
	public function safeDown()
	{
		$this->addColumn(ClientAccount::tableName(), 'credit_mgp', $this->integer()->notNull()->defaultValue(0));
	}
}

<?php

use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffVoipCountry;

/**
 * Class m181201_220501_update_uu_tariff_country
 */
class m181201_220501_update_uu_tariff_country extends \app\classes\Migration
{
	/**
	 * Up
	 */
	public function safeUp()
	{
		$this->_updateFk('CASCADE', 'CASCADE');
	}

	/**
	 * Down
	 */
	public function safeDown()
	{
		$this->_updateFk('RESTRICT', 'RESTRICT');
	}

	private function _updateFk($delete, $update)
	{
		$tariffVoipCountryTableName = TariffVoipCountry::tableName();
		$fkName = $tariffVoipCountryTableName . '_tariff_id_fk';
		$this->dropForeignKey($fkName, $tariffVoipCountryTableName);
		$this->addForeignKey($fkName, $tariffVoipCountryTableName, 'tariff_id', Tariff::tableName(), 'id', $delete, $update);
	}
}

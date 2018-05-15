<?php

use app\modules\uu\models\TariffPeriod;

/**
 * Class m180511_151751_change_column_to_uu_tariff_period
 */
class m180511_151751_change_column_to_uu_tariff_period extends \app\classes\Migration
{
	private $_column = 'price_min';

    /**
     * Up
     */
    public function safeUp()
    {
    	$this->alterColumn(TariffPeriod::tableName(), $this->_column, $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
	    $this->alterColumn(TariffPeriod::tableName(), $this->_column, $this->decimal(13,4));
    }
}

<?php

use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;

/**
 * Class m180518_125619_uu_drop_tariff
 */
class m180518_125619_uu_drop_tariff extends \app\classes\Migration
{
    private $_column = 'tariff_id';
    private $_foreignKey = 'tariff_id_fk';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->_mutualMigration();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->_mutualMigration(false);
    }

    /**
     * @param bool $withCascade
     */
    private function _mutualMigration($withCascade = true)
    {
        $tableName = TariffCountry::tableName();

        $this->dropForeignKey($this->_foreignKey, $tableName);
        $this->dropIndex($this->_foreignKey, $tableName);

        $this->createIndex($this->_foreignKey, $tableName, $this->_column);
        $this->addForeignKey(
            $this->_foreignKey,
            $tableName,
            $this->_column,
            Tariff::tableName(),
            'id',
            $withCascade ? 'CASCADE' : null,
            $withCascade ? 'CASCADE' : null
        );
    }
}

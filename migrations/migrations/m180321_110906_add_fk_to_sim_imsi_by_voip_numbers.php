<?php

use app\models\Number;
use app\modules\sim\models\Imsi;
use app\modules\uu\models\AccountTariff;

/**
 * Class m180321_110906_alter_voip_numbers_number_type
 */
class m180321_110906_add_fk_to_sim_imsi_by_voip_numbers extends \app\classes\Migration
{
    private $_fkSimImsiDid = 'fk-sim_imsi-did';
    private $_fkSimImsiMsisdn = 'sim_imsi_voip_numbers_number_fk';

    /**
     * Up
     */
    public function safeUp()
    {
        // Удаление внешнего ключа fk-sim_imsi-did из таблицы sim_imsi
        // (для изменения типа колонки did) с форматом varchar
        $this->dropForeignKey($this->_fkSimImsiDid, Imsi::tableName());
        $this->dropIndex($this->_fkSimImsiDid, Imsi::tableName());

        // Приводим колонки к типу bigint
        $this->alterColumn(Imsi::tableName(), 'did', $this->bigInteger());
        $this->alterColumn(Number::tableName(), 'number', $this->bigInteger());
        $this->alterColumn(AccountTariff::tableName(), 'voip_number', $this->bigInteger());

        // Создание внешнего ключа fk-sim_imsi-did из таблицы sim_imsi
        // (для изменения типа колонки did) с форматом bigint
        $this->addForeignKey($this->_fkSimImsiDid, Imsi::tableName(), 'did', Number::tableName(), 'number');

        $this->addForeignKey($this->_fkSimImsiMsisdn, Imsi::tableName(), 'msisdn', Number::tableName(), 'number');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->_fkSimImsiMsisdn, Imsi::tableName());
        $this->dropIndex($this->_fkSimImsiMsisdn, Imsi::tableName());

        // Удаление внешнего ключа fk-sim_imsi-did из таблицы sim_imsi
        // (для изменения типа колонки did) с форматом bigint
        $this->dropForeignKey($this->_fkSimImsiDid, Imsi::tableName());
        $this->dropIndex($this->_fkSimImsiDid, Imsi::tableName());

        // Приводим колонки к типу varchar
        $this->alterColumn(Number::tableName(), 'number', $this->string(15));
        $this->alterColumn(AccountTariff::tableName(), 'voip_number', $this->string(15));
        $this->alterColumn(Imsi::tableName(), 'did', $this->string(15));

        // Создание внешнего ключа fk-sim_imsi-did из таблицы sim_imsi
        // (для изменения типа колонки did) с форматом varchar
        $this->addForeignKey($this->_fkSimImsiDid, Imsi::tableName(), 'did', Number::tableName(), 'number');
    }
}

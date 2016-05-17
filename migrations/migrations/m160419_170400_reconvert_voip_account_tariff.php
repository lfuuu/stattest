<?php
require_once(__DIR__ . '/m160204_180300_convert_voip_account_tariff.php');

use app\classes\uu\model\AccountTariff;

/**
 * Заново сконвертировать услуги телефонии
 */
class m160419_170400_reconvert_voip_account_tariff extends m160204_180300_convert_voip_account_tariff
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // очистить ссылки из пакетов
        $accountTariffTableName = AccountTariff::tableName();
        $this->execute("UPDATE {$accountTariffTableName} 
        SET prev_account_tariff_id = NULL 
        WHERE prev_account_tariff_id IS NOT NULL");

        parent::safeDown(); // очистить
        parent::safeUp(); // сконвертировать
        $this->updateAccountTariffVoipNumber();
    }

    /**
     * Обоновить номера телефонов
     */
    public function updateAccountTariffVoipNumber()
    {
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("UPDATE
            {$accountTariffTableName},
            usage_voip
        SET
            {$accountTariffTableName}.voip_number = usage_voip.E164
        WHERE
          {$accountTariffTableName}.id = usage_voip.id + {$deltaVoipAccountTariff}
    ");
    }

}
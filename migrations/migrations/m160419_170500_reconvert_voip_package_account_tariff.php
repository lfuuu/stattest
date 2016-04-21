<?php
require_once(__DIR__ . '/m160211_172500_convert_voip_package_account_tariff.php');

/**
 * Заново сконвертировать услуги пакетов телефонии
 */
class m160419_170500_reconvert_voip_package_account_tariff extends m160211_172500_convert_voip_package_account_tariff
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        parent::safeDown(); // очистить
        parent::safeUp(); // сконвертировать
    }
}
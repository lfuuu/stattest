<?php

use app\models\Country;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffVoipCountry;

/**
 * Class m181003_210415_create_uu_tariff_country
 */
class m181003_210415_create_uu_tariff_country extends \app\classes\Migration
{
    /**
     * Up
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $tariffVoipCountryTableName = TariffVoipCountry::tableName();

        $this->createTable($tariffVoipCountryTableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'country_id' => $this->integer(4)->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey($tariffVoipCountryTableName . '_tariff_id_fk', $tariffVoipCountryTableName, 'tariff_id', Tariff::tableName(), 'id');
        $this->addForeignKey($tariffVoipCountryTableName . '_country_id_fk', $tariffVoipCountryTableName, 'country_id', Country::tableName(), 'code');

        $tariffCountryTableName = TariffCountry::tableName();
        $sql = "INSERT INTO {$tariffVoipCountryTableName} SELECT * FROM {$tariffCountryTableName}";
        TariffVoipCountry::getDb()->createCommand($sql)->execute();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tariffVoipCountryTableName = TariffVoipCountry::tableName();
        $this->dropTable($tariffVoipCountryTableName);
    }
}

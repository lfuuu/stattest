<?php

use app\models\Country;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;

/**
 * Class m180418_131117_create_uu_country
 */
class m180418_131117_create_uu_country extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $tariffCountryTableName = TariffCountry::tableName();
        $tariffTableName = Tariff::tableName();

        // create new
        $this->createTable($tariffCountryTableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'country_id' => $this->integer(4)->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey('tariff_id_fk', $tariffCountryTableName, 'tariff_id', $tariffTableName, 'id');
        $this->addForeignKey('country_id_fk', $tariffCountryTableName, 'country_id', Country::tableName(), 'code');

        // convert
        $sql = <<<SQL
            INSERT INTO {$tariffCountryTableName} (tariff_id, country_id)
            SELECT id, country_id FROM {$tariffTableName} WHERE country_id IS NOT NULL 
SQL;
        Tariff::getDb()->createCommand($sql)->execute();

        // drop old
        $this->dropForeignKey('fk-uu_tariff-country_id', $tariffTableName);
        $this->dropColumn($tariffTableName, 'country_id');
    }

    /**
     * Down
     *
     * @throws \yii\db\Exception
     */
    public function safeDown()
    {
        $tariffCountryTableName = TariffCountry::tableName();
        $tariffTableName = Tariff::tableName();

        // restore old
        $this->addColumn($tariffTableName, 'country_id', $this->integer());
        $this->addForeignKey('fk-uu_tariff-country_id', $tariffTableName, 'country_id', Country::tableName(), 'code');

        // convert back
        $sql = <<<SQL
            UPDATE {$tariffTableName}, {$tariffCountryTableName}
            SET {$tariffTableName}.country_id = {$tariffCountryTableName}.country_id
            WHERE {$tariffTableName}.id = {$tariffCountryTableName}.tariff_id
SQL;
        Tariff::getDb()->createCommand($sql)->execute();

        // drop new
        $this->dropTable($tariffCountryTableName);
    }
}

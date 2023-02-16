<?php

use app\classes\Migration;
use app\models\voip\Source;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffVoipSource;

/**
 * Class m230214_150423_uu_tariff_voip_source
 */
class m230214_150423_uu_tariff_voip_source extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(TariffVoipSource::tableName(), [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'source_code' => $this->string(32)->notNull(),
        ]);

        $this->addForeignKey(
            'fk-' . TariffVoipSource::tableName() . '-tariff_id',
            TariffVoipSource::tableName(), 'tariff_id',
            Tariff::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . TariffVoipSource::tableName() . '-source_code',
            TariffVoipSource::tableName(), 'source_code',
            Source::tableName(), 'code',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-' . TariffVoipSource::tableName() . '-tariff_id', TariffVoipSource::tableName());
        $this->dropForeignKey('fk-' . TariffVoipSource::tableName() . '-source_code', TariffVoipSource::tableName());
        $this->dropTable(TariffVoipSource::tableName());
    }
}

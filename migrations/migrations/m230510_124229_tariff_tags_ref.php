<?php

use app\classes\Migration;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffTags;

/**
 * Class m230510_124229_tariff_tags_ref
 */
class m230510_124229_tariff_tags_ref extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute(<<<SQL
with a as (
    select tg.id
    from uu_tariff_tags tg
             left join uu_tariff t on t.id = tg.tariff_id
    where t.id is null
)

delete from  uu_tariff_tags tg where id in (select id from a)
SQL
);
        $this->addForeignKey(
            'fk-' . TariffTags::tableName() . '-tariff_id--' . Tariff::tableName() . '-id',
            TariffTags::tableName(), 'tariff_id',
            Tariff::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-' . TariffTags::tableName() . '-tariff_id--' . Tariff::tableName() . '-id',
            TariffTags::tableName()
        );
    }
}

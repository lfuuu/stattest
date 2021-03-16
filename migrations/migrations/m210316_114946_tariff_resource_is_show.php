<?php

use app\classes\Migration;
use app\modules\uu\models\TariffResource;

/**
 * Class m210316_114946_tariff_resource_is_show
 */
class m210316_114946_tariff_resource_is_show extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(TariffResource::tableName(), 'is_show_resource', $this->integer()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(TariffResource::tableName(), 'is_show_resource');
    }
}

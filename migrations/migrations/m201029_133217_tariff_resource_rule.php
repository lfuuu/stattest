<?php

use app\classes\Migration;
use app\modules\uu\models\TariffResource;

/**
 * Class m201029_133217_tariff_resource_rule
 */
class m201029_133217_tariff_resource_rule extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(TariffResource::tableName(), 'is_can_manage', $this->tinyInteger()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(TariffResource::tableName(), 'is_can_manage');
    }
}

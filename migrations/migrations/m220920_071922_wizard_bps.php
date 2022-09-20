<?php

use app\classes\Migration;
use app\models\BusinessProcessStatus;

/**
 * Class m220920_071922_wizard_bps
 */
class m220920_071922_wizard_bps extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(
            BusinessProcessStatus::tableName(),
            'is_with_wizard',
            $this->tinyInteger()->notNull()->defaultValue(0)
        );

        $this->update(
            BusinessProcessStatus::tableName(),
            ['is_with_wizard' => 1],
            ['id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(
            BusinessProcessStatus::tableName(),
            'is_with_wizard'
        );
    }
}

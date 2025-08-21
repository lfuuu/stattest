<?php

/**
 * Class m250820_093935_voip_number_ms_teams
 */
class m250820_093935_voip_number_ms_teams extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\Number::tableName(), 'status',
            "ENUM ('notsale', 'instock', 'active_tested', 'active_commercial', 'notactive_reserved', 'notactive_hold', 'released', 'released_and_ported', 'active_connected', 'not_verfied', 'active_msteams') DEFAULT 'notsale' NOT NULL",
        );

        $this->addColumn(\app\models\Number::tableName(), 'is_in_msteams', $this->tinyInteger()->comment('Перенесен ли номер в MS Teams'));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\Number::tableName(), 'status',
            "enum ('notsale', 'instock', 'active_tested', 'active_commercial', 'notactive_reserved', 'notactive_hold', 'released', 'released_and_ported', 'active_connected', 'not_verfied') default 'notsale' not null",
        );
        $this->dropColumn(\app\models\Number::tableName(), 'is_in_msteams');
    }
}

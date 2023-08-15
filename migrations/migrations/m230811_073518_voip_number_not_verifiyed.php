<?php

/**
 * Class m230811_073518_voip_number_not_verifiyed
 */
class m230811_073518_voip_number_not_verifiyed extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\Number::tableName(), 'status',
            "enum('notsale','instock','active_tested','active_commercial','notactive_reserved','notactive_hold','released','released_and_ported','active_connected', 'not_verfied') NOT NULL DEFAULT 'notsale'"
        );

        $this->addColumn(\app\models\Number::tableName(), 'is_verified', $this->tinyInteger()->comment('был ли номер верифицирован'));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\Number::tableName(), 'status',
            "enum('notsale','instock','active_tested','active_commercial','notactive_reserved','notactive_hold','released','released_and_ported','active_connected') NOT NULL DEFAULT 'notsale'"
        );
    }
}


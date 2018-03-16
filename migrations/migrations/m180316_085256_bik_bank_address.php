<?php

use app\models\Bik;

/**
 * Class m180316_085256_bik_bank_address
 */
class m180316_085256_bik_bank_address extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bik::tableName(), 'bank_address', $this->string(255)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bik::tableName(), 'bank_address');
    }
}

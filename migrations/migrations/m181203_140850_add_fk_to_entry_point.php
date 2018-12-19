<?php

use app\models\EntryPoint;

/**
 * Class m181203_140850_add_fk_to_entry_point
 */
class m181203_140850_add_fk_to_entry_point extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(EntryPoint::tableName(), 'site_id', $this->integer());
        $this->addForeignKey(
            'fk-entry_point-public_site',
            EntryPoint::tableName(),
            'site_id',
            'public_site',
            'id',
            'SET NULL'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-entry_point-public_site', EntryPoint::tableName());
        $this->dropColumn(EntryPoint::tableName(), 'site_id');
    }
}

<?php
use app\models\InvoiceSettings;

/**
 * Class m170224_162515_not_vat_4_scheme
 */
class m170224_162515_not_vat_4_scheme extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // удалена 4 тема налогообложения
        $this->update(InvoiceSettings::tableName(), ['vat_apply_scheme' => 2], ['vat_apply_scheme' => 3]);
        $this->update(InvoiceSettings::tableName(), ['vat_apply_scheme' => 3], ['vat_apply_scheme' => 4]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // nothing
    }
}

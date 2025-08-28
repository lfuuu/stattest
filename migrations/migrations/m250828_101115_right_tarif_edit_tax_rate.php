<?php

/**
 * Class m250828_101115_right_tarif_edit_tax_rate
 */
class m250828_101115_right_tarif_edit_tax_rate extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update('user_rights', [
            'values' => 'read,edit,priceEdit,editTax',
            'values_desc' => 'чтение,изменение,редактирование прайс-листов,редактирование НДС',
        ], ['resource' => 'tarifs']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update('user_rights', [
            'values' => 'read,edit,priceEdit',
            'values_desc' => 'чтение,изменение,редактирование прайс-листов',
        ], ['resource' => 'tarifs']);
    }
}

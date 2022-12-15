<?php

/**
 * Class m221215_130023_voip_number_del_rule
 */
class m221215_130023_voip_number_del_rule extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update('user_rights', [
            'values' => 'access,admin,catalog,change-number-status,delete-number',
            'values_desc' => 'доступ,администрирование,справочники,изменение статуса номера,удаление номеров',
        ], ['resource' => 'voip']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update('user_rights', [
            'values' => 'access,admin,catalog,change-number-status',
            'values_desc' => 'доступ,администрирование,справочники,изменение статуса номера',
        ], ['resource' => 'voip']);
    }
}

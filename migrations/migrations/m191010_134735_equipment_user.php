<?php

use app\models\EquipmentUser;

/**
 * Class m191010_134735_equipment_user
 */
class m191010_134735_equipment_user extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(EquipmentUser::tableName(), [
            'id' => $this->primaryKey(),
            'client_account_id' => $this->integer()->notNull(),
            'full_name' => $this->string(1024)->notNull()->defaultValue(''),
            'birth_date' => $this->date()->notNull(),
            'passport' => $this->string()->notNull()->defaultValue(''),
            'passport_ext' => $this->string()->notNull()->defaultValue(''),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(EquipmentUser::tableName());
    }
}

<?php

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVm;

class m161025_113511_add_uu_tariff_vm_type extends \app\classes\Migration
{
    public function up()
    {
        $tableName = TariffVm::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->batchInsert($tableName, ['id', 'name'], [
            [2, 'Стандарт'],
            [3, 'Оптимум'],
            [4, 'Премиум'],
        ]);

        $tableName = Tariff::tableName();
        $fieldName = 'vm_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffVm::tableName(), 'id', 'RESTRICT');

    }

    public function down()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'vm_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);

        $tableName = TariffVm::tableName();
        $this->dropTable($tableName);
    }
}
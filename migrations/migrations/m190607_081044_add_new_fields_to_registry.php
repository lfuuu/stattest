<?php

use app\models\Number;
use app\models\UserRight;
use app\models\voip\Registry;

/**
 * Class m190607_081044_add_new_fields_to_registry
 */
class m190607_081044_add_new_fields_to_registry extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $registryTableName = Registry::tableName();
        $numberTableName = Number::tableName();

        $this->addColumn($registryTableName, 'solution_number', $this->string());
        $this->addColumn($registryTableName, 'numbers_count', $this->integer());
        $this->addColumn($registryTableName, 'solution_date', $this->string());

        $this->dropForeignKey($numberTableName . '-' . $registryTableName . '-fk', $numberTableName);
        $this->addForeignKey(
            $numberTableName . '-' . $registryTableName . '-fk',
            $numberTableName,
            'registry_id',
            $registryTableName,
            'id',
            'SET NULL'
        );

        $this->update(UserRight::tableName(), [
            'values' => 'access,admin,catalog,change-number-status',
            'values_desc' => 'доступ,администрирование,справочники,изменение статуса номера'
        ], [
            'resource' => 'voip'
        ]);

        $this->update(Registry::tableName(), ['numbers_count' => new \yii\db\Expression('number_full_to-number_full_from+1')]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $registryTableName = Registry::tableName();
        $numberTableName = Number::tableName();

        $this->update(UserRight::tableName(), [
            'values' => 'access,admin,catalog',
            'values_desc' => 'доступ,администрирование,справочники'
        ], [
            'resource' => 'voip'
        ]);


        $this->dropColumn($registryTableName, 'solution_number');
        $this->dropColumn($registryTableName, 'numbers_count');
        $this->dropColumn($registryTableName, 'solution_date');

        $this->dropForeignKey($numberTableName . '-' . $registryTableName . '-fk', $numberTableName);
        $this->addForeignKey(
            $numberTableName . '-' . $registryTableName . '-fk',
            $numberTableName,
            'registry_id',
            $registryTableName,
            'id'
        );
    }
}

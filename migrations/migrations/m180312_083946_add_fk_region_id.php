<?php

use app\models\ActualVirtpbx;
use app\models\City;
use app\models\Region;
use app\models\TariffVoip;
use app\models\TariffVoipPackage;

/**
 * Class m180312_083946_add_fk_region_id
 */
class m180312_083946_add_fk_region_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $regionTableName = Region::tableName();

        foreach ([TariffVoip::tableName(), TariffVoipPackage::tableName(), City::tableName()] as $tableName) {
            $this->addForeignKey(
                'fk-' . $tableName . '-connection_point_id-' . $regionTableName . '-id',
                $tableName, 'connection_point_id',
                $regionTableName, 'id'
            );
        }

        $this->addForeignKey(
            'fk-' . ActualVirtpbx::tableName() . '-region_id-' . Region::tableName() . '-id',
            ActualVirtpbx::tableName(), 'region_id',
            Region::tableName(), 'id'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $regionTableName = Region::tableName();

        foreach ([TariffVoip::tableName(), TariffVoipPackage::tableName(), City::tableName()] as $tableName) {
            $name = 'fk-' . $tableName . '-connection_point_id-' . $regionTableName . '-id';
            $this->dropForeignKey($name, $tableName);
            $this->dropIndex($name, $tableName);
        }

        $name = 'fk-' . ActualVirtpbx::tableName() . '-region_id-' . $regionTableName . '-id';
        $this->dropForeignKey($name, ActualVirtpbx::tableName());
        $this->dropIndex($name, ActualVirtpbx::tableName());
    }
}
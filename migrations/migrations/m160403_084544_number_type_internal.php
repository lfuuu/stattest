<?php

use app\models\Number;
use app\models\NumberType;

class m160403_084544_number_type_internal extends \app\classes\Migration
{
    public function up()
    {
        $this->update(Number::tableName(), ['number_type' => NumberType::ID_INTERNAL], ['number_type' => null]);

        $this->execute('ALTER TABLE `voip_number_type` ENGINE=InnoDB');
        $this->execute('ALTER TABLE `voip_number_type_country` ENGINE=InnoDB');

        $this->addForeignKey('fk-voip_numbers-number_type', 'voip_numbers', 'number_type', 'voip_number_type', 'id',
            'RESTRICT', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk-voip_numbers-number_type', 'voip_numbers');

        $this->execute('ALTER TABLE `voip_number_type` ENGINE=MyISAM');
        $this->execute('ALTER TABLE `voip_number_type_country` ENGINE=MyISAM');

        $this->update(Number::tableName(), ['number_type' => null], ['number_type' => NumberType::ID_INTERNAL]);
    }
}
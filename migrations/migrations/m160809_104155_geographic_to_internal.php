<?php

use app\models\Number;
use app\models\NumberType;
use app\models\voip\Registry;

class m160809_104155_geographic_to_internal extends \app\classes\Migration
{
    public function up()
    {
        $this->update(Registry::tableName(),['number_type_id' => NumberType::ID_GEO_DID], ['number_type_id' => NumberType::ID_INTERNAL__NOT_USED__]);
        $this->update(Number::tableName(),['number_type' => NumberType::ID_GEO_DID], ['number_type' => NumberType::ID_INTERNAL__NOT_USED__]);
        $this->delete(NumberType::tableName(), ['id' => NumberType::ID_INTERNAL__NOT_USED__]);
    }

    public function down()
    {
        $this->insert(NumberType::tableName(),['id' => NumberType::ID_INTERNAL__NOT_USED__, 'name' => 'Internal']);
    }
}
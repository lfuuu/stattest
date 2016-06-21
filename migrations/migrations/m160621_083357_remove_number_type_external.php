<?php

class m160621_083357_remove_number_type_external extends \app\classes\Migration
{
    public function up()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'status2');
        $nt = \app\models\NumberType::findOne(['id' => 8]);

        if (!$nt) {
            $this->insert(\app\models\NumberType::tableName(), ['id' => 8, 'name' => '7800']);
        }

        $this->update(\app\models\Number::tableName(), ['number_type' => 8], ['and', ['number_type' => 5], 'number like "7800%"']);
        $this->delete(\app\models\NumberType::tableName(), ['id' => 5]);
    }

    public function down()
    {
        $this->insert(\app\models\NumberType::tableName(), ['id' => 5, 'name' => 'Внешний']);
        $this->addColumn(\app\models\Number::tableName(), 'status2', $this->string());
    }
}
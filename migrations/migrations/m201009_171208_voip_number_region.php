<?php

/**
 * Class m201009_171208_voip_number_region
 */
class m201009_171208_voip_number_region extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Number::tableName(), 'nnp_region_id', $this->integer());
        $this->addColumn(\app\models\Number::tableName(), 'nnp_city_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'nnp_region_id');
        $this->dropColumn(\app\models\Number::tableName(), 'nnp_city_id');
    }
}

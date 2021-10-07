<?php

/**
 * Class m211007_100947_number_source_g4m
 */
class m211007_100947_number_source_g4m extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum ('portability', 'operator', 'regulator', 'innonet', 'voxbone', 'detached', 'portability_not_for_sale', 'operator_not_for_sale', 'portability_innonet', 'g4m') default 'portability' null");
        $this->alterColumn(\app\models\Number::tableName(),
            "source",
            "enum ('portability', 'operator', 'regulator', 'innonet', 'voxbone', 'detached', 'portability_not_for_sale', 'operator_not_for_sale', 'portability_innonet', 'g4m')      default 'operator' null");

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum ('portability', 'operator', 'regulator', 'innonet', 'voxbone', 'detached', 'portability_not_for_sale', 'operator_not_for_sale', 'portability_innonet') default 'portability' null");

        $this->alterColumn(\app\models\Number::tableName(),
            "source",
            "enum ('portability', 'operator', 'regulator', 'innonet', 'voxbone', 'detached', 'portability_not_for_sale', 'operator_not_for_sale', 'portability_innonet')      default 'operator' null");
    }
}

<?php

/**
 * Class m220614_131528_voip_source_Onderia
 */
class m220614_131528_voip_source_Onderia extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww', 'Onderia') DEFAULT 'portability'"
        );

        $this->alterColumn(\app\models\Number::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww', 'Onderia') DEFAULT 'portability'"
        );

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww') DEFAULT 'portability'"
        );

        $this->alterColumn(\app\models\Number::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww') DEFAULT 'portability'"
        );

    }
}

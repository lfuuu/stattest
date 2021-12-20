<?php

/**
 * Class m211217_183507_voip_source_www
 */
class m211217_183507_voip_source_www extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
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

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect') DEFAULT 'portability'"
        );
        $this->alterColumn(\app\models\Number::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect') DEFAULT 'portability'"
        );

    }
}

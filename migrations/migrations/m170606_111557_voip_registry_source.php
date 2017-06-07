<?php
use app\classes\enum\VoipRegistrySourceEnum;
use app\models\DidGroup;
use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m170606_111557_voip_registry_source
 */
class m170606_111557_voip_registry_source extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Registry::tableName(), 'source',
            "enum('portability','operator','regulator','" .
            VoipRegistrySourceEnum::PORTABILITY_NOT_FOR_SALE . "','" .
            VoipRegistrySourceEnum::OPERATOR_NOT_FOR_SALE . "') DEFAULT 'portability'");

        $this->addColumn(DidGroup::tableName(), 'is_service', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(Number::tableName(), 'is_service', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Registry::tableName(), 'source',
            "enum('portability','operator','regulator') DEFAULT 'portability'");

        $this->dropColumn(DidGroup::tableName(), 'is_service');
        $this->dropColumn(Number::tableName(), 'is_service');
    }
}

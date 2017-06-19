<?php
use app\models\TariffVoip;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;

/**
 * Class m170615_125432_usage_voip_ndc_type_id
 */
class m170615_125432_usage_voip_ndc_type_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageVoip::tableName(), 'ndc_type_id', $this->integer()->notNull()->defaultValue(NdcType::ID_GEOGRAPHIC));
        $this->update(UsageVoip::tableName(), ['ndc_type_id' => NdcType::ID_GEOGRAPHIC], ['type_id' => 'number']);
        $this->update(UsageVoip::tableName(), ['ndc_type_id' => NdcType::ID_FREEPHONE], ['type_id' => '7800']);
        $this->update(UsageVoip::tableName(), ['ndc_type_id' => NdcType::ID_MCN_LINE], ['type_id' => ['line', 'operator']]);
        $this->dropColumn(UsageVoip::tableName(), 'type_id');
        $this->update(TariffVoip::tableName(), ['ndc_type_id' => NdcType::ID_MCN_LINE], ['like', 'name', 'линия']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->addColumn(UsageVoip::tableName(), 'type_id', "enum('number','line','7800','operator') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER client");
        $this->update(UsageVoip::tableName(), ['type_id' => 'line'], ['ndc_type_id' => NdcType::ID_MCN_LINE]);
        $this->update(UsageVoip::tableName(), ['type_id' => '7800'], ['ndc_type_id' => NdcType::ID_FREEPHONE]);
        $this->update(UsageVoip::tableName(), ['type_id' => 'number'], ['NOT', ['ndc_type_id' => [NdcType::ID_MCN_LINE, NdcType::ID_FREEPHONE]]]);
        $this->dropColumn(UsageVoip::tableName(), 'ndc_type_id');
        $this->update(TariffVoip::tableName(), ['ndc_type_id' => NdcType::ID_GEOGRAPHIC], ['like', 'name', 'линия']);
    }
}

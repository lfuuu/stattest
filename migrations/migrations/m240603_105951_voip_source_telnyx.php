<?php

/**
 * Class m240603_105951_voip_source_telnyx
 */
class m240603_105951_voip_source_telnyx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(\app\models\voip\Source::tableName(), [
            'code' => 'telnyx',
            'name' => 'Telnyx',
            'is_service' => 0,
            'order' => 13,
        ]);

        $this->insert(\app\models\voip\Source::tableName(), [
            'code' => 'flowroute',
            'name' => 'FlowRoute',
            'is_service' => 0,
            'order' => 14,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(\app\models\voip\Source::tableName(), ['code' => ['telnyx', 'flowroute']]);
    }
}

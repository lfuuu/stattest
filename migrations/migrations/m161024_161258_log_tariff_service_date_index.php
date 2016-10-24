<?php

use app\models\LogTarif;
use app\models\UsageVoip;

class m161024_161258_log_tariff_service_date_index extends \app\classes\Migration
{
    private $field1 = 'service';
    private $field2 = 'date_activation';

    private $fieldActivationDt = 'activation_dt';
    private $fieldExpireDt = 'expire_dt';

    public function up()
    {
        $this->createIndex('idx-' . $this->field1 . '-' . $this->field2, LogTarif::tableName(), [$this->field1, $this->field2]);
        $this->createIndex('idx-' . $this->fieldActivationDt, UsageVoip::tableName(), $this->fieldActivationDt);
        $this->createIndex('idx-' . $this->fieldExpireDt, UsageVoip::tableName(), $this->fieldExpireDt);
    }

    public function down()
    {
        $this->dropIndex('idx-' . $this->field1 . '-' . $this->field2, LogTarif::tableName());
        $this->dropIndex('idx-' . $this->fieldActivationDt, UsageVoip::tableName());
        $this->dropIndex('idx-' . $this->fieldExpireDt, UsageVoip::tableName());
    }
}
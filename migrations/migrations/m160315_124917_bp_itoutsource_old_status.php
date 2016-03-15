<?php

use \app\models\BusinessProcessStatus;
use \app\models\BusinessProcess;

class m160315_124917_bp_itoutsource_old_status extends \app\classes\Migration
{
    public function up()
    {
        foreach ([
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_INCOMING => 'income',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_NEGOTIATIONS => 'negotiations',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_VERIFICATION => 'connecting',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_CONNECTING => 'connecting',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_ONSERVICE => 'work',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_SUSPENDED => 'suspended',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_FAILURE => 'tech_deny',
                     BusinessProcessStatus::ITOUTSOURSING_MAINTENANCE_TRASH => 'trash'

                 ] as $id => $status) {
            $this->update(BusinessProcessStatus::tableName(), ['oldstatus' => $status], ['id' => $id]);
        }
    }

    public function down()
    {
        $this->update(BusinessProcessStatus::tableName(),
            ['oldstatus' => ''],
            ['business_process_id' => BusinessProcess::ITOUTSOURSING_MAINTENANCE]
        );

        return true;
    }
}
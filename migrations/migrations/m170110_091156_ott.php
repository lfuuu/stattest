<?php
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;

/**
 * Class m170110_091156_ott
 */
class m170110_091156_ott extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(Business::tableName(), [
            'id' => Business::OTT,
            'name' => 'ОТТ',
            'sort' => 10
        ]);

        /*
            $this->insert(BusinessProcess::tableName(), [
                'id' => BusinessProcess::OTT_REPORTS,
                'business_id' => Business::OTT,
                'name' => 'Отчеты',
                'show_as_status' => 0,
                'sort' => 0
            ]);
        */

        $this->insert(BusinessProcess::tableName(), [
            'id' => BusinessProcess::OTT_SALES,
            'business_id' => Business::OTT,
            'name' => 'Продажи',
            'show_as_status' => 0,
            'sort' => 1
        ]);

        $this->insert(BusinessProcess::tableName(), [
            'id' => BusinessProcess::OTT_MAINTENANCE,
            'business_id' => Business::OTT,
            'name' => 'Сопровождение',
            'show_as_status' => 1,
            'sort' => 2
        ]);

        $map = [
            BusinessProcessStatus::OTT_MAINTENANCE_ORDER_OF_SERVICES => BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES,
            BusinessProcessStatus::OTT_MAINTENANCE_CONNECTED =>         BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
            BusinessProcessStatus::OTT_MAINTENANCE_WORK =>              BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
            BusinessProcessStatus::OTT_MAINTENANCE_DISCONNECTED =>      BusinessProcessStatus::TELEKOM_MAINTENANCE_DISCONNECTED,
            BusinessProcessStatus::OTT_MAINTENANCE_DISCONNECTED_DEBT => BusinessProcessStatus::TELEKOM_MAINTENANCE_DISCONNECTED_DEBT,
            BusinessProcessStatus::OTT_MAINTENANCE_TRASH =>             BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH,
            BusinessProcessStatus::OTT_MAINTENANCE_TECH_FAILURE =>      BusinessProcessStatus::TELEKOM_MAINTENANCE_TECH_FAILURE,
            BusinessProcessStatus::OTT_MAINTENANCE_FAILURE =>           BusinessProcessStatus::TELEKOM_MAINTENANCE_FAILURE,
            BusinessProcessStatus::OTT_MAINTENANCE_DUPLICATE =>         BusinessProcessStatus::TELEKOM_MAINTENANCE_DUPLICATE
        ];

        /** @var BusinessProcessStatus $bps */
        foreach ($map as $ottStatusId => $telekomStatusId) {
            $bps = BusinessProcessStatus::findOne(['id' => $telekomStatusId]);

            if (!$bps) {
                continue;
            }

            $newBps = new BusinessProcessStatus;
            $newBps->setAttributes($bps->getAttributes(), false);
            $newBps->id = $ottStatusId;
            $newBps->business_process_id = BusinessProcess::OTT_MAINTENANCE;
            $newBps->save();
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(BusinessProcessStatus::tableName(), ['business_process_id' => BusinessProcess::OTT_MAINTENANCE]);
        $this->delete(BusinessProcess::tableName(), ['business_id' => Business::OTT]);
        $this->delete(Business::tableName(), ['id' => Business::OTT]);
    }
}

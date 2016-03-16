<?php

use \app\models\Business;
use \app\models\BusinessProcess;
use \app\models\BusinessProcessStatus;

class m160315_101829_bp_it_outsource extends \app\classes\Migration
{
    public function up()
    {
        $businessId = Business::find()->max('id');
        $businessProcessId = BusinessProcess::find()->max('id');
        $businessProcessStatusId = BusinessProcessStatus::find()->max('id');

        $businessId++;

        $this->insert(Business::tableName(), [
            'id' => $businessId,
            'name' => 'ИТ-аутсорсинг',
            'sort' => $businessId
        ]);


        $businessProcessId++;

        $this->insert(BusinessProcess::tableName(), [
            'id' => $businessProcessId,
            'business_id' => $businessId,
            'name' => 'Сопровождение',
            'show_as_status' => 1,
            'sort' => 1
        ]);

        $sort = 0;
        foreach ([
                     'Входящие',
                     'В стадии переговоров',
                     'Проверка документов',
                     'Подключаемые',
                     'На обслуживании',
                     'Приостановленные',
                     'Отказ',
                     'Мусор'
                 ] as $statusName) {
            $businessProcessStatusId++;

            $this->insert(BusinessProcessStatus::tableName(), [
                'id' => $businessProcessStatusId,
                'business_process_id' => $businessProcessId,
                'name' => $statusName,
                'sort' => $sort++
            ]);
        }
    }

    public function down()
    {
        $business = Business::findOne(['name' => 'ИТ-аутсорсинг']);
        $businessProcess = BusinessProcess::findOne(['business_id' => $business->id]);

        if ($business) {
            $business->delete();
        }

        if ($businessProcess) {
            $businessProcess->delete();
            BusinessProcessStatus::deleteAll(['business_process_id' => $businessProcess->id]);
        }

        return true;
    }
}
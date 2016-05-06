<?php

use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;

class m160420_172903_operator_infrastructure_formal extends \app\classes\Migration
{
    public function up()
    {
        $this->update(BusinessProcessStatus::tableName(),
            ['sort' => (new \yii\db\Expression('sort+1'))],
            [
                'and',
                ['>=', 'sort', 4],
                ['business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE]
            ]
        );
        $this->insert(BusinessProcessStatus::tableName(), [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_FORMAL,
            'business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE,
            'sort' => 4,
            'name' => 'Формальные',
            'oldstatus' => '',
            'color' => ''
        ]);
    }

    public function down()
    {
        $this->delete(BusinessProcessStatus::tableName(), [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_FORMAL
        ]);

        $this->update(BusinessProcessStatus::tableName(),
            ['sort' => (new \yii\db\Expression('sort-1'))],
            [
                'and',
                ['>=', 'sort', 4],
                ['business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE]
            ]
        );
    }
}
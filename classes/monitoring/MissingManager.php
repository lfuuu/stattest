<?php

namespace app\classes\monitoring;

use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\BusinessProcessStatus;

class MissingManager extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'missing_manager';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Договора без менеджера';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'attribute' => 'id',
                'label' => 'ID договора',
            ],
            [
                'label' => 'Договор',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(
                        $data->contragent->name . ' / Договор № ' . $data->number . ' / ЛС № ' . $data->id,
                        ['/contract/edit', 'id' => $data->id]);
                },
            ],
        ];
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return new ArrayDataProvider([
            'allModels' =>
                ClientContract::find()
                    ->from(ClientContract::tableName() . ' cc')
                    ->leftJoin(ClientAccount::tableName() . ' c', 'c.contract_id = cc.id')
                    ->where(['manager' => ''])
                    ->andWhere([
                        'in',
                        'cc.business_process_status_id',
                        [
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES
                        ]
                    ])
                    ->groupBy('cc.id')
                    ->all(),
        ]);
    }

}
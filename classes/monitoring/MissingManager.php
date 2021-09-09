<?php

namespace app\classes\monitoring;

use app\classes\Html;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use yii\base\Component;
use yii\data\ArrayDataProvider;

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
                'width' => '100px',
            ],
            [
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->contragent->name, ['/contragent/edit', 'id' => $data->contragent->id], ['target' => '_blank']);
                },
                'width' => '30%',
            ],
            [
                'label' => '№ Договор',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->number, ['/contract/edit', 'id' => $data->id], ['target' => '_blank']);
                },
                'width' => '30%',
            ],
            [
                'label' => 'Лицевые счета',
                'format' => 'raw',
                'value' => function ($data) {
                    $accounts = [];
                    foreach ($data->clientAccountModels as $clientAccount) {
                        $accounts[] = Html::a('Л/С ' . $clientAccount->id,
                            ['/client/view', 'id' => $clientAccount->id],
                            ['target' => '_blank']
                        );
                    }
                    return implode(', ', $accounts);
                },
                'width' => '*',
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
                    ->alias('cc')
                    ->where(['manager' => ''])
                    ->andWhere([
                        'cc.business_process_status_id' => [
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                        ]
                    ])
                    ->groupBy('cc.id')
                    ->orderBy(['cc.id' => SORT_ASC])
                    ->all(),
        ]);
    }

}
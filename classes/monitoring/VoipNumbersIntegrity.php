<?php

namespace app\classes\monitoring;

use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;
use app\models\VoipNumber;
use app\models\UsageVoip;

class VoipNumbersIntegrity extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'voip_numbers_integrity';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Номера: неправильное состояние';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Расхождения между базой учета номеров и статусом услуги';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            MonitorGridColumns::getVoipNumber(),
            MonitorGridColumns::getVoipNumberStatus(),
            [
                'label' => 'Результат',
                'format' => 'raw',
                'value' => function($data) {
                    if ($data->status == 'instock' && $data->usageVoip->id) {
                        return
                            Html::tag('span', 'Используется ', ['style' => 'color: red;']) .
                            Html::a(
                                $data->usageVoip->clientAccount->contract->contragent->name .
                                ' / Договор № ' . $data->usageVoip->clientAccount->contract->number .
                                ' / ЛС № ' . $data->usageVoip->clientAccount->id,
                                ['/client/view', 'id' => $data->usageVoip->clientAccount->id],
                                ['target' => '_blank']

                            );
                    }
                    if ($data->status == 'active' && !$data->usageVoip->id) {
                        return Html::tag('span', 'Нет услуги', ['style' => 'color: blue;']);
                    }
                },
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $result = [];

        $result = array_merge(
            $result,
            VoipNumber::find()
                ->leftJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = number')
                ->where([VoipNumber::tableName() . '.status' => 'active'])
                ->andWhere('uv.id IS NULL')
                ->all()
        );

        $result = array_merge(
            $result,
            VoipNumber::find()
                ->rightJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = number')
                ->where([VoipNumber::tableName() . '.status' => 'instock'])
                ->andWhere(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->all()
        );

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}
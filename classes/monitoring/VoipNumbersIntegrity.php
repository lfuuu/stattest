<?php

namespace app\classes\monitoring;

use yii\db\Query;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;
use app\models\UsageVoip;
use app\models\Number;

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
                    $usage = UsageVoip::findOne($data['usage_id']);

                    if ($data['status'] == Number::NUMBER_STATUS_INSTOCK && $usage->id) {
                        return
                            Html::tag('span', 'Используется ', ['style' => 'color: red;']) .
                            Html::a(
                                $usage->clientAccount->contract->contragent->name .
                                ' / Договор № ' . $usage->clientAccount->contract->number .
                                ' / ЛС № ' . $usage->clientAccount->id,
                                ['/client/view', 'id' => $usage->clientAccount->id],
                                ['target' => '_blank']

                            );
                    }
                    if (!$data['usage_id']) {
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
            (new Query)
                ->select(['vn.*', 'uv.id AS usage_id'])
                ->from([Number::tableName() . ' vn'])
                ->leftJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = vn.number')
                ->where(['vn.status' => Number::NUMBER_STATUS_ACTIVE])
                ->andWhere('uv.id IS NULL')
                ->all()
        );

        $result = array_merge(
            $result,
            (new Query)
                ->select(['vn.*', 'uv.id AS usage_id'])
                ->from([UsageVoip::tableName() . ' uv'])
                ->leftJoin(Number::tableName() . ' vn', 'vn.number = uv.E164')
                ->where(['uv.type_id' => 'number'])
                ->andWhere(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->andWhere(['!=', 'vn.status', Number::NUMBER_STATUS_ACTIVE])
                ->all()
        );

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}
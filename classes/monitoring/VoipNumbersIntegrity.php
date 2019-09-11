<?php

namespace app\classes\monitoring;

use app\classes\Html;
use app\models\Number;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;

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
                'value' => function ($data) {


                    if ($data['usage_id']) {
                        $usage = UsageVoip::findOne(['id' => $data['usage_id']]);
                    } elseif ($data['account_tariff_id']) {
                        $usage = AccountTariff::findOne(['id' => $data['account_tariff_id']]);
                    } else {
                        return Html::tag('span', 'Нет услуги', ['style' => 'color: blue;']);
                    }
                    $clientAccount = $usage->clientAccount;

                    if ($data['status'] == Number::STATUS_INSTOCK && $usage->id) {
                        return
                            Html::a('Используется ', $usage->getUrl(), ['style' => 'color: red;', 'target' => '_blank']) . '&nbsp;' .
                            Html::a(
                                $clientAccount->contract->contragent->name .
                                ' / Договор № ' . $clientAccount->contract->number .
                                ' / ' . $clientAccount->getAccountTypeAndId(),
                                ['/client/view', 'id' => $clientAccount->id],
                                ['target' => '_blank']
                            );
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
                ->select(['vn.*', 'usage_id' => 'uv.id', 'account_tariff_id' => 'at.id'])
                ->from([Number::tableName() . ' vn'])
                ->leftJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = vn.number AND CAST(NOW() AS DATE) between uv.actual_from AND uv.actual_to')
                ->leftJoin(AccountTariff::tableName() . ' at', 'at.voip_number = vn.number AND at.tariff_period_id IS NOT NULL')
                ->where(['vn.status' => Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]])
                ->andWhere(['uv.id' => null, 'at.id' => null])
                ->all()
        );

        $result = array_merge(
            $result,
            (new Query)
                ->select(['vn.*', 'usage_id' => 'uv.id', 'account_tariff_id' => 'at.id'])
                ->from([Number::tableName() . ' vn'])
                ->leftJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = vn.number AND CAST(NOW() AS DATE) between uv.actual_from AND uv.actual_to')
                ->leftJoin(AccountTariff::tableName() . ' at', 'at.voip_number = vn.number AND at.tariff_period_id IS NOT NULL')
                ->andWhere(['not in', 'vn.status', Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]])
                ->andWhere(['NOT', ['uv.id' => null, 'at.id' => null]])
                ->all()
        );

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}
<?php

namespace app\classes\monitoring;

use app\models\ClientContract;
use app\modules\uu\models\AccountTariff;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;

class DisabledContractWithServices extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'DisabledContractWithServices';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Отключеные договора и включенные услуги';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Отключеные договора и включенные услуги';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'ЛС',
                'format' => 'raw',
                'value' => function ($data) {
                    return
                        Html::a(
                            $data['client_account_id'],
                            ['/client/view', 'id' => $data['client_account_id']],
                            ['target' => '_blank']
                        );
                }
            ],
            [
                'label' => 'Статус договора',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['status_color'] ?
                        '<b style="background:' . $data['status_color'] . ';">' . $data['status_name'] . '</b>' :
                        '<b>' . $data['status_name'] . '</b>';
                }
            ],

            [
                'label' => 'ID услуги',
                'format' => 'raw',
                'value' => function ($data) {
                    return
                        Html::a(
                            $data['account_tariff_id'],
                            AccountTariff::getUrlById( $data['account_tariff_id']),
                            ['target' => '_blank']
                        );
                },

            ],            [
                'label' => 'Тип услуги',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['service_name'];
                },

            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['contragent_name'];
                }
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $result = AccountTariff::find()
            ->alias('a')
            ->joinWith('clientAccount.clientContractModel cc', true, 'INNER JOIN')
            ->joinWith('clientAccount.clientContractModel.clientContragent cg', true, 'INNER JOIN')
            ->joinWith('clientAccount.clientContractModel.businessProcessStatus bps', true, 'INNER JOIN')
            ->joinWith('serviceType s', true, 'INNER JOIN')
            ->select([
                'client_account_id',
                'account_tariff_id' => 'a.id',
                'service_name' => 's.name',
                'status_name' => 'bps.name',
                'status_color' => 'bps.color',
                'contragent_name' => 'cg.name',
            ])
            ->where([
                's.parent_id' => null,
                'cc.business_process_status_id' => ClientContract::$offBPSids
            ])
            ->andWhere(['IS NOT', 'tariff_period_id', NULL])
            ->orderBy([
                's.id' => SORT_ASC,
                'client_account_id' => SORT_ASC,
                'a.id' => SORT_ASC
            ])
            ->asArray()
            ->all();

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}
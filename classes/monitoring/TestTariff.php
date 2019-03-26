<?php

namespace app\classes\monitoring;

use app\modules\uu\models\ServiceType;
use yii\base\Component;
use app\classes\Html;
use app\models\ClientAccount;
use yii\data\ArrayDataProvider;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;

class TestTariff extends Component implements MonitoringInterface
{
    const DELTA_DAYS = 10;

    /**
     * @return string
     */
    public function getKey()
    {
        return 'test_tariff';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Test tariff';
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
                'label' => 'Лицевые счета',
                'format' => 'raw',
                'value' => 'client_link',
                'width' => '*',
            ],
            [
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => 'contragent_name',
                'width' => '30%',
            ],
            [
                'label' => 'Услуга',
                'format' => 'raw',
                'value' => function ($data)
                {
                    return Html::a($data['tariff_name'], $data['tariff_url']);
                },
            ],
            [
                'label' => 'Менеджер',
                'format' => 'raw',
                'value' => 'manager',
            ],
            [
                'label' => 'Аккаунт менеджер',
                'format' => 'raw',
                'value' => 'account_manager',
            ],
        ];
    }

    public function getResult()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $deltaDays = self::DELTA_DAYS;

        $testStatuses = implode(', ', Tariff::getTestStatuses());
        if (!$testStatuses) {
            return 0;
        }

        $sql = <<<SQL
            SELECT
                account_tariff.client_account_id,
                account_tariff.id
            FROM
                {$accountTariffTableName} account_tariff
                        LEFT JOIN {$clientAccountTableName} client ON client.id = account_tariff.client_account_id
                        LEFT JOIN {$tariffPeriodTableName} tariff_period ON tariff_period.id = account_tariff.tariff_period_id
                        LEFT JOIN {$tariffTableName} tariff ON tariff.id = tariff_period.tariff_id
            WHERE
                client.voip_credit_limit_day != 0
                AND tariff.tariff_status_id IN ({$testStatuses})
                AND account_tariff.insert_time + INTERVAL tariff.count_of_validity_period day + INTERVAL {$deltaDays} day < NOW()
            GROUP BY account_tariff.client_account_id
SQL;

        $db = AccountTariff::getDb();

        $rows = $db->createCommand($sql)->queryAll();

        $data = array_combine(array_column($rows, 'client_account_id'), array_column($rows, 'id'));
        $clientAccountsQuery = ClientAccount::find()->andWhere(['id' => array_keys($data)]);

        $result = [];
        foreach ($clientAccountsQuery->each() as $clientAccount) {
            /** @var ClientAccount $clientAccount */
            $accountTariff = AccountTariff::findOne($data[$clientAccount->id]);
            $clientContractModel = $clientAccount->clientContractModel;
            $contragent = $clientContractModel->contragent;
            if (!$contragent || !$accountTariff) {
                continue;
            }

            $result[] = [
                'contragent_id' => $contragent->id,
                'contragent_name' => $contragent->name ?: $contragent->name_full,
                'service_type_id' => $accountTariff->service_type_id,
                'tariff_url' => $accountTariff->getUrl(),
                'tariff_name' => $accountTariff->getNameLight(),
                'client_link' => $clientAccount->getLink(),
                'manager' => $clientContractModel->manager,
                'account_manager' => $clientContractModel->account_manager
            ];
        }

        return new ArrayDataProvider([
            'allModels' => $result
        ]);
    }
}
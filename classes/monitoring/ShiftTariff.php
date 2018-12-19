<?php

namespace app\classes\monitoring;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Component;
use app\classes\Html;
use yii\data\ArrayDataProvider;

use app\models\HistoryChanges;
use app\modules\uu\models\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;

class ShiftTariff extends Component implements MonitoringInterface
{
    /**
     * @return string
     */
    public function getKey()
    {
        return 'shift_tariff';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Shift tariff';
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
        $historyChangesTableName = HistoryChanges::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        $sql = <<<SQL
            SELECT
                account_tariff_log.account_tariff_id
            FROM
                {$accountTariffLogTableName} account_tariff_log,
                (
                    SELECT DISTINCT model_id
                    FROM
                        {$historyChangesTableName}
                    WHERE
                        model = :model
                        AND action = :action
                        AND created_at > :date
                ) t
            WHERE
	            account_tariff_log.id = t.model_id
SQL;
        $db = AccountTariffLog::getDb();
        $rows = $db->createCommand($sql, [
            ':model' => AccountTariffLog::class,
            ':action' => HistoryChanges::ACTION_UPDATE,
            ':date' => DateTimeZoneHelper::getUtcDateTime()->modify('-1 day')
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
        ])->queryAll();

        $ids = !$rows ? array_column($rows, 'account_tariff_id') : [];

        $result = [];
        $query = AccountTariff::find()->where(['id' => $ids]);
        foreach ($query->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $clientAccount = $accountTariff->clientAccount;
            $clientContractModel = $clientAccount->clientContractModel;
            $contragent = $clientContractModel->contragent;
            if (!$contragent) {
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

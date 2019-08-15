<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffStatus;

/**
 * Class m190710_161245_uu_calls_term
 */
class m190711_161245_uu_calls_term extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (Resource::findOne(['id' => Resource::ID_TRUNK_PACKAGE_TERM_CALLS])) {
            return;
        }

        // update calls orig resource
        $this->update(
            Resource::tableName(),
            [
                'name' => 'Звонки (оригинация)',
            ],
            [
                'id' => Resource::ID_TRUNK_PACKAGE_ORIG_CALLS,
            ]
        );

        // insert calls term resource
        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_TRUNK_PACKAGE_TERM_CALLS,
            'name' => 'Звонки (терминация)',
            'unit' => '¤',
            'min_value' => 0.000000,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_TRUNK_PACKAGE_TERM,
        ]);

        // get all term tariffs which has no tariff_resource
        $tariffTable = \app\modules\uu\models\Tariff::tableName();
        $tariffResourceTable = TariffResource::tableName();
        $termTariffIds = \app\modules\uu\models\Tariff::find()
            ->select($tariffTable . '.id')
            ->leftJoin($tariffResourceTable, $tariffResourceTable . '.tariff_id=' . $tariffTable . '.id')
            ->where(['service_type_id' => ServiceType::ID_TRUNK_PACKAGE_TERM])
            ->andWhere(['!=', 'tariff_status_id', TariffStatus::ID_ARCHIVE])
            ->andWhere([$tariffResourceTable . '.id' => null])
            ->column();

        // get account_tariff with these tariffs with active previous account tariff
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTable = TariffPeriod::tableName();
        $accountTariffs = AccountTariff::find()
            ->innerJoin($tariffPeriodTable, $tariffPeriodTable. '.id=' . $accountTariffTableName . '.tariff_period_id')
            ->innerJoin(
                $accountTariffTableName . ' uat_prev',
                'uat_prev.id=' . $accountTariffTableName . '.prev_account_tariff_id'
                    . ' and uat_prev.client_account_id=' . $accountTariffTableName . '.client_account_id'
            )
            ->with('tariffPeriod')
            ->where([$tariffPeriodTable . '.tariff_id' => $termTariffIds])
            ->andWhere(['IS NOT', 'uat_prev.tariff_period_id', null])
            ->all();

        $processed = [];
        /** @var AccountTariff[] $accountTariffs */
        foreach ($accountTariffs as $accountTariff) {
            $tariffId = $accountTariff->tariffPeriod->tariff_id;
            if (!empty($processed[$tariffId])) {
                continue;
            }
            $processed[$tariffId] = $tariffId;

            // check for test only
            if (!$accountTariff->isTestForOperationCost()) {
                continue;
            }

            // insert tariff_resource
            $this->insert(TariffResource::tableName(), [
                'amount' => 0.000000,
                'price_per_unit' => 1.000000,
                'price_min' => 0.000000,
                'resource_id' => Resource::ID_TRUNK_PACKAGE_TERM_CALLS,
                'tariff_id' => $tariffId,
            ]);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        if (!Resource::findOne(['id' => Resource::ID_TRUNK_PACKAGE_TERM_CALLS])) {
            return;
        }

        $this->delete(TariffResource::tableName(), [
            'resource_id' => [Resource::ID_TRUNK_PACKAGE_TERM_CALLS]
        ]);

        $this->delete(Resource::tableName(), [
            'id' => [Resource::ID_TRUNK_PACKAGE_TERM_CALLS]
        ]);
    }
}

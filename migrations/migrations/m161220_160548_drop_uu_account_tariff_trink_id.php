<?php
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;

/**
 * Class m161220_160548_drop_uu_account_tariff_trink_id
 */
class m161220_160548_drop_uu_account_tariff_trink_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function up()
    {
        // временно удалить FK
        $usageTrunkServiceTableName = UsageTrunkSettings::tableName();
        $this->dropForeignKey($usageTrunkServiceTableName . '__usag_id', $usageTrunkServiceTableName);

        // удалить AUTO_INCREMENT
        $usageTrunkTableName = UsageTrunk::tableName();
        $this->alterColumn($usageTrunkTableName, 'id', $this->integer());

        // вернуть FK
        $this->addForeignKey($usageTrunkServiceTableName . '__usag_id', $usageTrunkServiceTableName, 'usage_id', $usageTrunkTableName, 'id');

        /** @var AccountTariff[] $accountTariffs */
        $accountTariffs = AccountTariff::find()
            ->where(['service_type_id' => ServiceType::ID_TRUNK])
            ->all();
        foreach ($accountTariffs as $accountTariff) {
            $usageTrunk = new UsageTrunk;
            $usageTrunk->id = $accountTariff->id;
            $usageTrunk->client_account_id = $accountTariff->client_account_id;
            $usageTrunk->connection_point_id = $accountTariff->region_id;
            $usageTrunk->trunk_id = $accountTariff->trunk_id;
            $usageTrunk->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
            $usageTrunk->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
            $usageTrunk->status = UsageTrunk::STATUS_CONNECTING;
            if (!$usageTrunk->save()) {
                throw new LogicException(implode(' ', $usageTrunk->getFirstErrors()));
            }
        }

        // удалить поле из УУ
        $accountTariffTableName = AccountTariff::tableName();
        $this->dropColumn($accountTariffTableName, 'trunk_id');
    }

    /**
     * Down
     */
    public function down()
    {
        return false;
    }
}
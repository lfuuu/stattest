<?php

use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;

/**
 * Class m170919_172157_add_uu_mobile_outbound
 */
class m170919_172157_add_uu_mobile_outbound extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_VOIP_MOBILE_OUTBOUND,
            'name' => 'Исх. моб. связь',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VOIP,
        ]);

        $resource = Resource::findOne(['id' => Resource::ID_VOIP_MOBILE_OUTBOUND]);
        $this->addTariffResource($resource);
    }

    /**
     * Down
     *
     * @throws \Exception
     */
    public function safeDown()
    {
        $resource = Resource::findOne(['id' => Resource::ID_VOIP_MOBILE_OUTBOUND]);
        $resource->deleteTariffResource();
    }

    /**
     * Добавить этот ресурс в тариф
     *
     * @param \app\modules\uu\models\Resource $resource
     * @throws \yii\db\Exception
     */
    public function addTariffResource($resource)
    {
        $db = Resource::getDb();
        $resourceId = $resource->id;
        $serviceTypeId = $resource->service_type_id;

        $tariffTableName = Tariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();

        $sql = <<<SQL
            INSERT INTO {$tariffResourceTableName}
                (amount, price_per_unit, price_min, resource_id, tariff_id)
            SELECT 
                IF(id IN (10238, 10399, 10400, 10737, 10756, 10776, 10781, 10782), 1, 0) AS amount, 
                IF(id IN (10775, 10755), 149, 0) AS pricePerUnit, 
                0 AS priceMin,
                {$resourceId}, 
                id
            FROM {$tariffTableName}
            WHERE service_type_id = {$serviceTypeId};
SQL;
        $db->createCommand($sql)->execute();


        if ($resource->isOption()) {
            $sql = <<<SQL
            INSERT INTO {$accountTariffResourceLogTableName}
                (account_tariff_id, resource_id, amount, actual_from_utc, insert_time, insert_user_id)
            SELECT
                {$accountTariffLogTableName}.account_tariff_id,
                {$resourceId},
                IF({$tariffTableName}.id=10775, 1, 0) AS amount,
                {$accountTariffLogTableName}.actual_from_utc,
                {$accountTariffLogTableName}.insert_time,
                {$accountTariffLogTableName}.insert_user_id
            FROM
                {$accountTariffLogTableName}, 
                {$tariffPeriodTableName}, 
                {$tariffTableName}
            WHERE 
                {$accountTariffLogTableName}.tariff_period_id IS NOT NULL
                AND {$accountTariffLogTableName}.tariff_period_id = {$tariffPeriodTableName}.id
                AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
                AND {$tariffTableName}.service_type_id = {$serviceTypeId};
SQL;
            $db->createCommand($sql)->execute();
        }
    }

}

<?php

namespace app\modules\uu\behaviors;

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class ResourceFiller extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function afterInsert(Event $event)
    {
        /** @var \app\modules\uu\models\Resource $resource */
        $resource = $event->sender;

        $tariffTableName = Tariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();

        $db = $resource::getDb();

        $sql = <<<SQL
            INSERT INTO {$tariffResourceTableName}
                (amount, price_per_unit, price_min, resource_id, tariff_id)
            SELECT 0, {$resource->fillerPricePerUnit}, 0, {$resource->id}, id
            FROM {$tariffTableName}
            WHERE service_type_id = {$resource->service_type_id}
SQL;
        $db->createCommand($sql)->execute();

        if ($resource->isOption()) {
            $sql = <<<SQL
                INSERT INTO {$accountTariffResourceLogTableName}
                    (account_tariff_id, resource_id, amount, actual_from_utc, insert_time, insert_user_id)
                SELECT
                    {$accountTariffLogTableName}.account_tariff_id,
                    {$resource->id},
                    0,
                    {$accountTariffLogTableName}.actual_from_utc,
                    {$accountTariffLogTableName}.insert_time,
                    {$accountTariffLogTableName}.insert_user_id
                FROM {$accountTariffLogTableName}, {$tariffPeriodTableName}, {$tariffTableName}
                WHERE {$accountTariffLogTableName}.tariff_period_id IS NOT NULL
                    AND {$accountTariffLogTableName}.tariff_period_id = {$tariffPeriodTableName}.id
                    AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
                    AND {$tariffTableName}.service_type_id = {$resource->service_type_id}
SQL;
            $db->createCommand($sql)->execute();
        }
    }

    /**
     * @param Event $event
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function beforeDelete(Event $event)
    {
        /** @var \app\modules\uu\models\Resource $resource */
        $resource = $event->sender;

        $accountEntryTableName = AccountEntry::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();
        $tariffResourceTableName = TariffResource::tableName();

        $db = $resource::getDb();

        // лог ресурсов
        $sql = <<<SQL
            DELETE
                {$accountLogResourceTableName}.*
            FROM
                {$accountLogResourceTableName},
                {$tariffResourceTableName}
            WHERE
                {$tariffResourceTableName}.resource_id = {$resource->id}
                AND {$tariffResourceTableName}.id = {$accountLogResourceTableName}.tariff_resource_id
SQL;
        $db->createCommand($sql)->execute();

        // транзакции ресурсов
        AccountTariffResourceLog::deleteAll(['resource_id' => $resource->id]);

        // ресурсы тарифов
        TariffResource::deleteAll(['resource_id' => $resource->id]);

        // проводки
        $sql = <<<SQL
            DELETE FROM
                {$accountEntryTableName}
            WHERE
                type_id > 0
                AND type_id NOT IN (SELECT id FROM {$tariffResourceTableName})
SQL;
        $db->createCommand($sql)->execute();
    }
}

<?php

use app\classes\api\ApiCore;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVpbx;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use app\models\EventQueue;
use app\models\UsageVirtpbx;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

class VirtPbx3Checker
{
    const LOAD_ACTUAL = 'actual';
    const LOAD_SAVED = 'saved';

    public static function check($usageId = 0, $isSync = true)
    {
        l::ll(__CLASS__, __FUNCTION__);

        if ($diff = self::diff(
            self::load(self::LOAD_SAVED, $usageId),
            self::load(self::LOAD_ACTUAL, $usageId))
        ) {
            if ($isSync) {
                VirtPbx3Diff::apply($diff);
            } else {
                VirtPbx3Diff::makeEvents($diff);
            }
        }
    }

    private static $sqlActual = "
            SELECT * FROM (
                SELECT
                    u.id as usage_id,
                    c.id as client_id,
                    IFNULL((SELECT id_tarif AS id_tarif FROM log_tarif WHERE service='usage_virtpbx' AND id_service=u.id AND date_activation<NOW() ORDER BY date_activation DESC, id DESC LIMIT 1),0) AS tarif_id,
                    u.region as region_id,
                    prev_usage_id,
                    (select ifnull((select id from usage_virtpbx where prev_usage_id = u.id), (select id from uu_account_tariff where prev_usage_id = u.id AND uu_account_tariff.service_type_id = :serviceTypeId))) AS next_usage_id,
                    :version_biller_usage as biller_version
                FROM
                    usage_virtpbx u, clients c
                WHERE
                    # actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') AND actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                    (now() between activation_dt and expire_dt)
                    AND u.client = c.client
                
                UNION 
                
                SELECT
                    account_tariff.id AS usage_id,
                    account_tariff.client_account_id AS client_id,
                    tariff_period.tariff_id,
                    account_tariff.region_id,
                    prev_usage_id,
                    (select ifnull((select id from usage_virtpbx where prev_usage_id = account_tariff.id), (select id from uu_account_tariff where prev_usage_id = account_tariff.id AND uu_account_tariff.service_type_id = :serviceTypeId))) AS next_usage_id,
                    :version_biller_universal AS biller_version
                FROM
                    uu_account_tariff account_tariff,
                    uu_tariff_period tariff_period,
                    clients client
                WHERE
                    tariff_period.id = account_tariff.tariff_period_id
                    AND client.id = account_tariff.client_account_id
                    AND account_tariff.service_type_id = 1
                    AND tariff_period_id is not null
                    AND client.account_version = :version_biller_universal
            ) a
            ORDER BY usage_id, biller_version
            ";

    private static $sqlSaved =
        "SELECT usage_id, client_id, tarif_id, region_id
        FROM actual_virtpbx
        order by usage_id";

    private static function load($type, $usageId = 0)
    {
        l::ll(__CLASS__, __FUNCTION__, $type);

        switch ($type) {
            case self::LOAD_ACTUAL:
                $sql = self::$sqlActual;
                break;
            case self::LOAD_SAVED:
                $sql = self::$sqlSaved;
                break;
            default:
                throw new Exception("Unknown type");
        }

        $d = [];
        $query = Yii::$app->getDb()->createCommand($sql);

        if ($type == self::LOAD_ACTUAL) {
            $query->bindValue(':version_biller_usage', ClientAccount::VERSION_BILLER_USAGE);
            $query->bindValue(':version_biller_universal', ClientAccount::VERSION_BILLER_UNIVERSAL);
            $query->bindValue(':serviceTypeId', ServiceType::ID_VPBX);
        }

        $usageIds = [];

        if ($usageId) {
            $usageIds[$usageId] = 1;
            $usage = UsageVirtpbx::findOne(['id' => $usageId]) ?: AccountTariff::findOne(['id' => $usageId]);
            if ($usage) {
                if ($usage->prev_usage_id) {
                    $usageIds[$usage->prev_usage_id] = 1;
                }

                $nextUsageId = Yii::$app->db->createCommand("
                    SELECT IFNULL(
                        (
                            SELECT id 
                            FROM uu_account_tariff 
                            WHERE prev_usage_id = :nextUsageId
                            AND service_type_id = :serviceTypeId
                            LIMIT 1 
                        ),
                        (
                            SELECT id
                            FROM usage_virtpbx 
                            WHERE prev_usage_id = :nextUsageId
                            LIMIT 1
                        )
                    )
                ", [':nextUsageId' => $usageId, ':serviceTypeId' => ServiceType::ID_VPBX])->queryScalar();

                if ($nextUsageId) {
                    $usageIds[$nextUsageId] = 1;
                }
            }
        }

        foreach ($query->query() as $l) {
            if (!$usageIds || isset($usageIds[$l["usage_id"]])) {
                $d[$l["usage_id"]] = $l;

            }
        }

        if (!$usageId && !$d) {
            throw new Exception("Data not load");
        }

        return $d;
    }

    private static function diff($saved, $actual)
    {
        l::ll(__CLASS__, __FUNCTION__,/*$saved, $actual,*/
            "...", "...");

        $d = [
            "added" => [],
            "deleted" => [],
            "changed_data" => [],
            "changed_client" => []
        ];

        foreach (array_diff(array_keys($saved), array_keys($actual)) as $l) {
            $d["deleted"][$l] = ['action' => 'del'] + $saved[$l];
        }

        foreach (array_diff(array_keys($actual), array_keys($saved)) as $l) {
            $d["added"][$l] = ['action' => 'add'] + $actual[$l];
        }

        if ($d["added"] && $d["deleted"]) {
            foreach ($d["added"] as $addId => $add) {
                if ($add["prev_usage_id"] && isset($d["deleted"][$add["prev_usage_id"]])) {
                    $d["changed_client"][$addId] = ['action' => 'changed_client'] + $add;
                    unset($d["added"][$addId], $d["deleted"][$add["prev_usage_id"]]);
                }
            }
        }


        foreach ($actual as $usageId => $l) {
            if (isset($saved[$usageId])) {
                if (
                    $saved[$usageId]["tarif_id"] != $l["tarif_id"]
                    ||
                    $saved[$usageId]["region_id"] != $l["region_id"]
                ) {
                    $d["changed_data"][$usageId] = ['action' => 'changed_data'] + $l + [
                            "prev_tarif_id" => $saved[$usageId]["tarif_id"],
                            "prev_region_id" => $saved[$usageId]["region_id"],
                        ];
                }
            }
        }

        foreach ($d as $k => $v) {
            if ($v) {
                return $d;
            }
        }

        return false;
    }
}

class VirtPbx3
{
    private static $_api = null;

    /**
     * Входная функция для синхронизации одной ВАТС
     *
     * @param int $usageId ID услуги
     * @throws Exception
     */
    public static function sync($usageId = 0)
    {
        l::ll(__CLASS__, __FUNCTION__);
        if (!$usageId) {
            throw new Exception('usageId not set');
        }
        VirtPbx3Checker::check($usageId, true);
    }

    public static function check($usageId = 0)
    {
        l::ll(__CLASS__, __FUNCTION__);
        VirtPbx3Checker::check($usageId, false);
    }

    public static function getNumberTypes($clientId)
    {
        if (ApiPhone::me()->isAvailable()) {
            try {
                return ApiPhone::me()->numbersState($clientId);
            } catch (Exception $e) {
                trigger_error2($e->getMessage());
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param ApiVpbx $api
     */
    public static function setApi(ApiVpbx $api)
    {
        self::$_api = $api;
    }

    /**
     * @return ApiVpbx
     */
    public static function getApi()
    {
        if (!self::$_api) {
            self::setApi(ApiVpbx::me());
        }

        return self::$_api;
    }
}

class VirtPbx3Diff
{
    public static function apply(&$diff)
    {
        l::ll(__CLASS__, __FUNCTION__, $diff);
        $exception = null;

        if ($diff["added"]) {
            self::add($diff["added"], $exception);
        }

        if ($diff["deleted"]) {
            self::del($diff["deleted"], $exception);
        }

        if ($diff["changed_client"]) {
            self::clientChanged($diff["changed_client"], $exception);
        }

        if ($diff["changed_data"]) {
            self::dataChanged($diff["changed_data"], $exception);
        }

        if ($exception instanceof Exception) {
            throw $exception;
        }
    }

    /**
     * Функция генерирует события на синхронизацию отдельных ВАТСов с платформой
     * @param $diff
     */
    public static function makeEvents($diff)
    {
        foreach ($diff as $type => $data) {
            foreach ($data as $value) {
                EventQueue::go(EventQueue::SYNC__VIRTPBX3, $value);
            }
        }
    }

    private static function add(&$d, &$exception)
    {
        l::ll(__CLASS__, __FUNCTION__, $d);

        foreach ($d as $l) {
            try {
                VirtPbx3Action::add($l);
            } catch (Exception $e) {
                if (!$exception) {
                    $exception = $e;
                }
            }
        }
    }

    private static function del(&$d, &$exception)
    {
        l::ll(__CLASS__, __FUNCTION__, $d);

        foreach ($d as $l) {
            try {
                VirtPbx3Action::del($l);
            } catch (Exception $e) {
                if (!$exception) {
                    $exception = $e;
                }
            }
        }
    }

    private static function clientChanged(&$d, &$exception)
    {
        l::ll(__CLASS__, __FUNCTION__, $d);

        foreach ($d as $l) {
            try {
                VirtPbx3Action::clientChanged($l);
            } catch (Exception $e) {
                if (!$exception) {
                    $exception = $e;
                }
            }
        }
    }

    private static function dataChanged(&$d, &$exception)
    {
        l::ll(__CLASS__, __FUNCTION__, $d);

        foreach ($d as $l) {
            try {
                VirtPbx3Action::dataChanged($l);
            } catch (Exception $e) {
                if (!$exception) {
                    $exception = $e;
                }
            }
        }
    }

}

class VirtPbx3Action
{
    const ADD = 'add';
    const DEL = 'del';

    public static function add(&$l)
    {
        l::ll(__CLASS__, __FUNCTION__, $l);

        if (!defined("AUTOCREATE_VPBX") || !AUTOCREATE_VPBX) {
            return null;
        }

        $uuUsage = null;
        $usage = UsageVirtpbx::findOne(['id' => $l['usage_id']]);
        if (!$usage) {
            $uuUsage = AccountTariff::findOne(['id' => $l['usage_id']]);
            if (!$uuUsage) {
                return null;
            }
        }

        if (self::_checkTransfer(self::ADD, $usage, $uuUsage)) {
            $msg = 'Создается переносимая ВАТС';

            l::ll(__CLASS__, __FUNCTION__, $msg);
            Yii::error($msg . PHP_EOL . print_r($l, true));
            throw new LogicException($msg);
        }

        if (VirtPbx3::getApi()->isAvailable()) {
            $exceptionVpbx = null;
            try {

                VirtPbx3::getApi()->create($l["client_id"], $l["usage_id"], $l['biller_version']);

            } catch (Exception $e) {
                $exceptionVpbx = $e;
            }

            if ($exceptionVpbx) {
                throw $exceptionVpbx;
            }
        }

        $row = new ActualVirtpbx();
        $row->usage_id = $l["usage_id"];
        $row->client_id = $l["client_id"];
        $row->tarif_id = $l["tarif_id"];
        $row->region_id = $l["region_id"];
        $row->biller_version = $l['biller_version'];

        return $row->save();
    }

    public static function del(&$l)
    {
        l::ll(__CLASS__, __FUNCTION__, $l);

        if (!defined("AUTOCREATE_VPBX") || !AUTOCREATE_VPBX || !VirtPbx3::getApi()->isAvailable()) {
            return null;
        }

        $uuUsage = null;
        $usage = UsageVirtpbx::findOne(['id' => $l['usage_id']]);
        if (!$usage) {
            $uuUsage = AccountTariff::findOne(['id' => $l['usage_id']]);
            if (!$uuUsage) {
                return null;
            }
        }

        if (self::_checkTransfer(self::DEL, $usage, $uuUsage)) {
            $msg = 'Удаляется переносимая ВАТС';
            l::ll(__CLASS__, __FUNCTION__, $msg);
            Yii::error($msg . PHP_EOL . print_r($l, true));
            throw new LogicException($msg);
        }

        try {
            VirtPbx3::getApi()->archiveVpbx($l["client_id"], $l["usage_id"]);
        } catch (Exception $e) {
            if ($e->getCode() != ApiCore::ERROR_PRODUCT_NOT_EXSISTS) {
                throw $e;
            }
        }

        $row = ActualVirtpbx::findOne(['usage_id' => $l["usage_id"]]);
        if ($row) {
            return $row->delete();
        }

        return false;
    }

    public static function clientChanged($l)
    {
        l::ll(__CLASS__, __FUNCTION__, $l);

        $toUsage = UsageVirtpbx::findOne(['id' => $l["usage_id"]]) ?: AccountTariff::findOne(['id' => $l["usage_id"]]);
        if (!$toUsage) {
            return;
        }

        $fromUsage = UsageVirtpbx::findOne(['id' => $toUsage->prev_usage_id]) ?: AccountTariff::findOne(['id' => $toUsage->prev_usage_id]);

        if (!$fromUsage) {
            return;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();

        try {
            if (VirtPbx3::getApi()->isAvailable()) {

                // $numInfo = ApiPhone::me()->getNumbersInfo($fromUsage->clientAccount);
                VirtPbx3::getApi()->transfer(
                    $fromUsage->clientAccount->id,
                    $fromUsage->id,
                    $toUsage->clientAccount->id,
                    $toUsage->id
                );
            }

            $row = ActualVirtpbx::findOne([
                "usage_id" => $fromUsage->id,
                "client_id" => $fromUsage->clientAccount->id
            ]);

            if ($row) {
                $row->usage_id = $toUsage->id;
                $row->client_id = $toUsage->clientAccount->id;
                $row->save();
            }

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    public static function dataChanged($l)
    {
        l::ll(__CLASS__, __FUNCTION__, $l);

        try {
            VirtPbx3::getApi()->update($l["client_id"], $l["usage_id"], $l["region_id"], $l['biller_version']);
        } catch (Exception $e) {
            throw $e;
        }

        $row = ActualVirtpbx::findOne([
            "client_id" => $l["client_id"],
            "usage_id" => $l["usage_id"]
        ]);

        if ($row) {
            $row->tarif_id = $l["tarif_id"];
            $row->region_id = $l["region_id"];

            return $row->save();
        }

        return false;
    }

    /**
     * @param string $action
     * @param UsageVirtpbx $usage
     * @param AccountTariff $uuUsage
     * @return bool
     */
    private static function _checkTransfer($action, $usage, $uuUsage)
    {
        if ($action == self::ADD) {
            $prevUsageId = $usage ? $usage->prev_usage_id : $uuUsage->prev_usage_id;

            if (!$prevUsageId) {
                return false;
            }

            // добавление. Предыдущая услуга ещё работает
            $where = ['id' => $prevUsageId];
            return (bool)(UsageVirtpbx::find()->where($where)->actual()->one() ?:
                AccountTariff::findOne($where + ['IS NOT', 'tariff_period_id', null]));
        }

        // Удаление переносимой услуги
        $usageId = $usage ? $usage->id : $uuUsage->id;
        $where = ['prev_usage_id' => $usageId];
        return (bool)(UsageVirtpbx::find()->where($where)->exists() ?:
            AccountTariff::find()->where($where + ['service_type_id' => ServiceType::ID_VPBX])->exists());
    }

}

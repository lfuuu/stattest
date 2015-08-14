<?php

use app\classes\api\ApiCore;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVpbx;

class VirtPbx3Checker
{
    public static function check($usageId = 0)
    {
        l::ll(__CLASS__,__FUNCTION__);

        $actual = self::load("actual", $usageId);

        if($diff = self::diff(self::load("saved", $usageId), $actual))
            VirtPbx3Diff::apply($diff);
    }

    private static $sqlActual = "
            SELECT
                u.id as usage_id,
                c.id as client_id,
                IFNULL((SELECT id_tarif AS id_tarif FROM log_tarif WHERE service='usage_virtpbx' AND id_service=u.id AND date_activation<NOW() ORDER BY date_activation DESC, id DESC LIMIT 1),0) AS tarif_id
            FROM
                usage_virtpbx u, clients c
            WHERE
                    actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') 
                AND actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                AND u.client = c.client 
                AND (   
                           c.status IN ('work','negotiations','connecting','testing','debt','blocked','suspended') 
                        OR c.client = 'id9130'
                        )
            ORDER BY u.id";

    private static $sqlSaved=
        "SELECT usage_id, client_id, tarif_id
        FROM actual_virtpbx
        order by usage_id";

    private function load($type, $usageId = 0)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);
        global $db, $db_ats;

        switch($type)
        {
            case 'actual': $sql = self::$sqlActual; break;
            case 'saved':  $sql = self::$sqlSaved;  break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        foreach($db->AllRecords($sql) as $l) {
            if (!$usageId || $usageId == $l["usage_id"]) {
                $d[$l["usage_id"]] = $l;
            }
        }

        if (!$usageId && !$d)
            throw new Exception("Data not load");

        return $d;
    }

    private function diff(&$saved, &$actual)
    {
        l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = array(
                "added" => array(), 
                "deleted" => array(), 
                "changed_tarif" => array(), 
                );

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
            $d["deleted"][$l] = $saved[$l];

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];

        foreach($actual as $usageId => $l)
            if(isset($saved[$usageId]) && $saved[$usageId]["tarif_id"] != $l["tarif_id"]) 
                $d["changed_tarif"][$usageId] = $l + array("prev_tarif_id" => $saved[$usageId]["tarif_id"]);

        foreach($d as $k => $v)
            if($v)
                return $d;

        return false;
    }
}

class VirtPbx3
{
    public static function check($usageId = 0)
    {
        l::ll(__CLASS__,__FUNCTION__);
        VirtPbx3Checker::check($usageId);
    }


    public static function getNumberTypes($clientId)
    {
        if (ApiPhone::isAvailable()) {
            try {
                return ApiPhone::exec('numbers_state', ['account_id' => $clientId]);
            }catch(Exception $e)
            {
                trigger_error2($e->getMessage());
                return [];
            }
        } else {
            return [];
        }
    }
}

class VirtPbx3Diff
{
    public static function apply(&$diff)
    {
        l::ll(__CLASS__,__FUNCTION__,$diff);
        $exception = null;

        if($diff["added"])
            self::add($diff["added"], $exception);

        if($diff["deleted"])
            self::del($diff["deleted"], $exception);

        if($diff["changed_tarif"])
            self::tarifChanged($diff["changed_tarif"], $exception);

        if ($exception instanceof Exception) {
            throw $exception;
        }
    }

    private function add(&$d, &$exception)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $l) {
            try {
                VirtPbx3Action::add($l);
            } catch (Exception $e) {
                if (!$exception) $exception = $e;
            }
        }
    }

    private function del(&$d, &$exception)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $l) {
            try {
                VirtPbx3Action::del($l);
            } catch (Exception $e) {
                if (!$exception) {
                    $exception = $e;
                }
            }
        }
    }

    private function tarifChanged(&$d, &$exception)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $l) {
            try {
                VirtPbx3Action::tarifChanged($l);
            } catch (Exception $e) {
                if (!$exception) $exception = $e;
            }
        }
    }

}

class VirtPbx3Action
{
    public static function add(&$l)
    {
        global $db;

        l::ll(__CLASS__,__FUNCTION__, $l);

        if (!defined("AUTOCREATE_VPBX") || !AUTOCREATE_VPBX) {
            return null;
        }

        $exceptionProduct = null;
        try {
            $vpbxIP =
                $db->GetValue("
                SELECT
                    s.ip
                FROM usage_virtpbx u
                INNER JOIN datacenter d ON d.region = u.region
                INNER JOIN server_pbx s ON s.datacenter_id = d.id
                WHERE u.id = {$l["usage_id"]}
            ");

            $newState = array("server_host" => (defined("VIRTPBX_TEST_ADDRESS") ? VIRTPBX_TEST_ADDRESS : $vpbxIP), "mnemonic" => "vpbx", "stat_product_id" => $l["usage_id"]);

            ApiCore::exec('add_products_from_stat', SyncCoreHelper::getAddProductStruct($l["client_id"], $newState));

        } catch (Exception $e) {
            $exceptionProduct = $e;
        }

        $exceptionVpbx = null;
        try {

            ApiVpbx::create($l["client_id"], $l["usage_id"]);

        } catch (Exception $e) {
            $exceptionVpbx = $e;
        }

        if ($exceptionProduct) {
            throw $exceptionProduct;
        }

        if ($exceptionVpbx) {
            throw $exceptionVpbx;
        }

        return $db->QueryInsert("actual_virtpbx", array(
                "usage_id" => $l["usage_id"],
                "client_id" => $l["client_id"],
                "tarif_id" => $l["tarif_id"],
            )
        );
    }

    public static function del(&$l)
    {
        global $db;

        l::ll(__CLASS__,__FUNCTION__, $l);

        if (!defined("AUTOCREATE_VPBX") || !AUTOCREATE_VPBX) {
            return null;
        }

        try {

            ApiCore::exec('remove_product', SyncCoreHelper::getRemoveProductStruct($l["client_id"], 'vpbx') + ["stat_product_id" => $l["usage_id"]]);

        } catch (Exception $e) {
            if ($e->getCode() != ApiCore::ERROR_PRODUCT_NOT_EXSISTS)
            {
                throw $e;
            }
        }

        return $db->QueryDelete("actual_virtpbx", array(
                "usage_id" => $l["usage_id"],
            )
        );

    }

    public static function tarifChanged($l)
    {
        global $db;

        l::ll(__CLASS__,__FUNCTION__, $l);

        try {

            ApiVpbx::updateTariff($l["client_id"], $l["usage_id"]);

        } catch (Exception $e) {
            throw $e;
        }

        $db->QueryUpdate("actual_virtpbx", "client_id", array(
            "usage_id" => $l["usage_id"],
            "client_id" => $l["client_id"],
            "tarif_id" => $l["tarif_id"],
        ));

    }

}

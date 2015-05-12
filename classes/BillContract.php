<?php

namespace app\classes;

use app\classes\DateFunction;
use app\models\ClientContract;

class BillContract
{
    public static function getBillItemString($clientId, $date = null)
    {
        if ( $date === null)
            $date = time();

        $contract = self::getString($clientId, $date);

        if($contract)
            return ", согласно Договора ".$contract;

        return "";
    }

    public static function getString($clientId, $date)
    {
        $contract = self::getLastContract($clientId, $date);

        if($contract)
            return $contract["no"]." от ".DateFunction::mdate($contract["date"], "d месяца Y") . " г.";

        return "";
    }

    public static function getLastContract($clientId, $dateTs)
    {
        return ClientContract::getDb()->createCommand("
            select 
                contract_no as no, 
                unix_timestamp(contract_date) as date 
            from 
                client_contracts 
            where 
                    client_id = :client_id
                and contract_date <= FROM_UNIXTIME(:date_ts)
                and type = 'contract'
            order by is_active desc, contract_date desc, id desc 
            limit 1", [":client_id" => $clientId, ":date_ts" => $dateTs])->queryOne();
    }
}

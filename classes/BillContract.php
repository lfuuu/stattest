<?php

namespace app\classes;

use app\classes\DateFunction;
use app\models\ClientDocument;

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

    public static function getLastContract($contractId, $dateTs)
    {
        return ClientDocument::getDb()->createCommand("
            select 
                contract_no as no, 
                unix_timestamp(contract_date) as date 
            from 
                client_document
            where 
                    contract_id = :contract_id
                and contract_date <= FROM_UNIXTIME(:date_ts)
                and type = 'contract'
            order by is_active desc, contract_date desc, id desc 
            limit 1", [":contract_id" => $contractId, ":date_ts" => $dateTs])->queryOne();
    }
}

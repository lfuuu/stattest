<?php

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
            return $contract["no"]." от ".mdate("d месяца Y",$contract["date"]) . " г.";

        return "";
    }
    public static function getLastContract($clientId, $dateTs)
    {
        global $db;
        return $db->GetRow("
            select 
                contract_no as no, 
                unix_timestamp(contract_date) as date 
            from 
                client_contracts 
            where 
                    client_id = ".$clientId." 
                and contract_date <= FROM_UNIXTIME('".$dateTs."')
                and type = 'contract'
            order by is_active desc, contract_date desc, id desc 
            limit 1");
    }
}

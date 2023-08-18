<?php

namespace app\classes;

use app\classes\DateFunction;
use app\models\ClientContract;
use app\models\ClientDocument;

class BillContract
{
    public static function getBillItemString($clientId, $date = null)
    {
        $contract = self::getString($clientId, $date);

        if ($contract) {
            return ", согласно Договора " . $contract;
        }

        return "";
    }

    public static function getString($contractId, $date)
    {
        $contract = self::getLastContract($contractId, $date);

        if ($contract) {
            return $contract["no"] . " от " . DateFunction::mdate($contract["date"], "d месяца Y") . " г.";
        }

        return $contractId;
    }

    public static function getLastContract($contractId, $dateTs, $isWithBN = true)
    {
        if (!$dateTs) {
            $dateTs = time();
        }

        $data = ClientDocument::getDb()->createCommand("
            SELECT 
                contract_no as no, 
                UNIX_TIMESTAMP(contract_date) as date 
            FROM 
                client_document
            WHERE 
                    contract_id = :contract_id
                and contract_date <= FROM_UNIXTIME(:date_ts)
                and type = 'contract'
            ORDER BY is_active DESC, contract_date DESC, id DESC 
            limit 1", [":contract_id" => $contractId, ":date_ts" => $dateTs])->queryOne();

        if ($isWithBN && ClientContract::find()->where(['id' => $contractId])->select('state')->scalar() == ClientContract::STATE_OFFER) {
            $data['no'] = 'б/н';
        }

        return $data;
    }
}

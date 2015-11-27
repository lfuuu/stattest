<?php 

use app\models\ClientAccount;
use app\models\ClientCounter;

class Api
{
    public static function getBalance($clientIds, $simple = true)
    {
        if (!is_array($clientIds)) {
            $clientIds = (array) $clientIds;
        }

        foreach ($clientIds as $clientId) {
            if(!$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
                throw new Exception('Неверный номер лицевого счета!');
            }
        }

        $result = [];
        foreach ($clientIds as $clientId) {
            $clientAccount = ClientAccount::findOne([is_numeric($clientId) ? 'id' : 'client' => $clientId]);

            if (!($clientAccount instanceof ClientAccount)) {
                throw new Exception('Лицевой счет не найден!');
            }

            $balance = $clientAccount->balance;
            $credit = $clientAccount->credit;
            $expenditure = ClientCounter::dao()->getAmountSumByAccountId($clientAccount->id);

            if ($credit >= 0) {
                $balance += $expenditure['amount_sum'];
            }

            $result[$clientAccount->id] = [
                'id' => $clientAccount->id,
                'balance' => $balance,
                'credit' => $credit,
                'expenditure' => $expenditure,
                'currency' => $clientAccount->currency,
                'view_mode' => $clientAccount->lk_balance_view_mode,
            ];
        }

        if ($simple) {
            $clientId = $clientIds[0];
            return $result[$clientId]['balance'];
        }

        return $result;
    }

    /**
    * Возвращает все активные номера лицевого счета
    *
    * @param int $clientId id лицевого счета
    * @param bool выдать простой массив с номерами, или полный, с детальной информацией
    * @return array
    */
    public static function getClientPhoneNumbers($clientId, $isSimple = false)
    {
        global $db;

        $clientId = (int)$clientId;

        if (!$clientId)
            throw new Exception("Лицевой счет не найден!");

        $data = array();
        foreach($db->AllRecords("
                    SELECT E164, 
                    no_of_lines,
                    (select count(*) from vpbx_numbers v where (v.client_id = c.id and v.number = E164)) as is_vpbx
                    FROM 
                        `usage_voip` u, clients c 
                    where 
                            c.id = '".$clientId."' 
                        and c.client = u.client 
                        and actual_from < cast(now() as date) 
                        and actual_to >= cast(now() as date)") as $l)
        {
            if ($isSimple)
            {
                $data[$l["E164"]] = 1;
            } else {
                $data[] = array("number" => $l["E164"], "lines" => $l["no_of_lines"], "on_the_vpbx" => $l["is_vpbx"] ? 1 : 0);
            }
        }
        return  $data;
    }

    /**
    * Устанавливает, какие номера используются в vpbx'е
    *
    * @param int id лицевого счета
    * @param array массив номеров
    * @return bool
    */
    public static function setClientVatsPhoneNumbers($clientId, $numbers)
    {
        global $db;

        $clientId = (int)$clientId;

        if (!$clientId)
            throw new Exception("Лицевой счет не найден!");

        $clientNumbers = self::getClientPhoneNumbers($clientId, true);

        $db->Query("start transaction");
        $db->Query("delete from vpbx_numbers where client_id = '".$clientId."'");

        foreach($numbers as $number)
        {
            $number = preg_replace("/[^\d]/", "", $number);

            if (!$number || !isset($clientNumbers[$number]))
            {
                $db->Query("rollback");
                throw new Exception("Номер \"".$number."\" не найден в номерах клиента!");
            }
            $db->QueryInsert("vpbx_numbers", array("client_id" => $clientId, "number" => $number));
        }
        $db->Query("commit");
        return true;
    }
}
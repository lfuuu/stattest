<?php

class ApiVpbx
{

    /**
    * Возвращает все активные номера лицевого счета
    *
    * @param int $clientId id лицевого счета
    * @param bool выдать простой массив с номерами, или полный, с детальной информацией
    * @return array
    */
    public static function getClientPhoneNumbers($clientId, $isSimple = false)
    {
        global $db_ats;

        $clientId = (int)$clientId;

        if (!$clientId)
            throw new Exception("Лицевой счет не найден!");

        $data = array();
        foreach($db_ats->AllRecords("SELECT number, call_count from a_number where client_id = '".$clientId."' and enabled = 'yes'") as $l)
        {
            if ($isSimple)
            {
                $data[$l["number"]] = 1;
            } else {
                $isVpbx = $db_ats->GetValue("SELECT count(1) FROM `a_virtpbx_link` l, a_virtpbx v, a_number n where l.virtpbx_id = v.id and v.client_id = '".$clientId."' and l.type='number' and n.id = type_id and v.client_id = n.client_id and n.number = '".$l["number"]."'");
                $data[] = array("number" => $l["number"], "lines" => $l["call_count"], "on_the_vpbx" => $isVpbx ? 1 : 0);
            }
        }

        return  $data;
    }

    public static function addDid($clientId, $phone)
    {
        // check cleint && exists phone number
        $clientNumbers = self::getClientPhoneNumbers($clientId, true);
        if (!isset($clientNumbers[$phone])) 
        {
            throw new Exception("Неизвестный номер", 501);
        }

        //already added => answer: ok
        $list = virtPbx::getList($clientId);
        foreach($list["numbers"] as $number)
        {
            if ($number["number"] == $phone)
            {
                return 'ok';
            }
        }

        //add

        $isPass = false;
        try{
            $r = SyncVirtPbx::addDid($clientId, $phone);
        }catch(Exception $e)
        {
            if ($e->getCode() != 514) // Номер "7499xxxxxxx" уже используется
            {
                throw $e;
            }
            $isPass = true;
        }

        if (isset($r["success"]) || $isPass)
        {
            virtPbx::addNumber($clientId, $phone);

            return "ok";
        } else {
            //unknown error
            virtPbx::delNumber($clientId, $phone);
            SyncVirtPbx::delDid($clientId, $phone);
        }


        return "error";
    }

    public static function delDid($clientId, $phone)
    {
        // check cleint && exists phone number
        $clientNumbers = self::getClientPhoneNumbers($clientId, true);
        if (!isset($clientNumbers[$phone])) 
        {
            throw new Exception("Неизвестный номер");
        }

        $isDeleted = true;
        $list = virtPbx::getList($clientId);
        foreach($list["numbers"] as $number)
        {
            if ($number["number"] == $phone)
            {
                $isDeleted = false;
            }
        }

        if ($isDeleted)
            return 'ok';

        $isPass = false;
        try{
            $r = SyncVirtPbx::delDid($clientId, $phone);
        }catch(Exception $e)
        {
            if ($e->getCode() != 514) // Номер "7xxxxxxxxxx" не существует
            {
                throw $e;
            }
            $isPass = true;
        }

        if (isset($r["success"]) || $isPass)
        {
            virtPbx::delNumber($clientId, $phone);

            return 'ok';
        }

        return 'error';
    }

    /**
    * Получаем статистику по занятому пространству
    *
    * @param $clientId int id лицевого счета
    * @param $data array данные запроса, для форирования статистики
    * @return int занятое просторанство
    */
    public static function getUsageSpaceStatistic($clientId, $data)
    {
        return SyncVirtPbx::getStatistic($clientId, $data, "get_total_space_usage", "total");
    }

    /**
    * Получаем статистику по количеству используемых портов
    *
    * @param $clientId int id лицевого счета
    * @param $data array данные запроса, для форирования статистики
    * @return int кол-во используемых портов
    */
    public static function getUsageNumbersStatistic($clientId, $data)
    {
        return SyncVirtPbx::getStatistic($clientId, $data, "get_int_number_usage", "int_number_amount");
    }

    /**
    * Устанавливает, какие номера используются в vpbx'е
    *
    * @param int id лицевого счета
    * @param array массив номеров
    * @return bool
    */
    public static function ______setClientVatsPhoneNumbers($clientId, $numbers)
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

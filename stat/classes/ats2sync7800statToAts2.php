<?php

/**
 * Класс синхронизации привязанных линий без номера к номерам 7800, из стата в ats2
 *
 * @author Andreev Dmitriy
 */

class ats2sync7800statToAts2
{
    /**
     * Функция запуска синхронизации
     */
    public static function sync()
    {
        $statData = self::load_fromStat();
        $atsData = self::load_fromATS();

        if ( $diff = self::diff($statData, $atsData) )
        {
            $queries = self::diffToQuery($diff);

            if ( $queries )
            {
                self::execQueryies($queries);
            }
        }
    }

    /**
     * Загружем данные со СТАТа
     */
    private static function load_fromStat()
    {
        global $db;

        $data = array();
        foreach( $db->AllRecords(
                    "SELECT 
                        c.id as client_id, 
                        u.E164 as number7800, 
                        u2.E164 as line_nonum 
                    FROM 
                        clients c, `usage_voip` u 
                    INNER JOIN `usage_voip` u2 on (u2.id = u.line7800_id)
                    where 
                        c.client = u.client 
                        and CAST(now() as DATE) between u.actual_from and u.actual_to 
                        and u.E164 like '7800%' 
                        and u.line7800_id != 0") as $l)
        {
            $data[$l["client_id"]][$l["number7800"]] = $l["line_nonum"];
        }

        return $data;
    }

    /**
     * Загружаем данные из базы АТС
     */
    private static function load_fromATS()
    {
        global $db_ats;

        $data = array();

        foreach( $db_ats->AllRecords(
                    "SELECT
                        ln.client_id,
                        ifnull(a7800.number, 0) as number7800,
                        ifnull(anonum.number, 0) as line_nonum
                    FROM
                        a_7800_line ln
                    LEFT JOIN a_number a7800 ON (ln.number7800_id = a7800.id and ln.client_id = a7800.client_id)
                    LEFT JOIN a_number anonum ON (ln.line_nonum_id = anonum.id and ln.client_id = anonum.client_id)
                    ") as $l )
        {
            $data[$l["client_id"]][$l["number7800"]] = $l["line_nonum"];
        }

        return $data;
    }

    /**
     * Функция сравнения данных
     */
    private static function diff($stat, $ats)
    {
        $diff = array();

        $statCl = array_keys($stat);
        $atsCl = array_keys($ats);

        $addCl = array_diff($statCl, $atsCl); //add
        $delCl = array_diff($atsCl, $statCl); //del

        // сравниваем данные внутри клиента
        foreach(array_intersect($statCl, $atsCl) as $clientId)
        {
            $statNums = $stat[$clientId];
            $atsNums = $ats[$clientId];

            $statNumKeys = array_keys($statNums);
            $atsNumKeys = array_keys($atsNums);

            $addNums = array_diff($statNumKeys, $atsNumKeys);
            $delNums = array_diff($atsNumKeys, $statNumKeys);

            // сравниваем изменения в привязке линий
            foreach (array_intersect($statNumKeys, $atsNumKeys) as $number)
            {
                if ($statNums[$number] != $atsNums[$number])
                {
                    $diff["number_update"][$clientId][$number] = $statNums[$number];
                }

            }

            if ($addNums)
                foreach ($addNums as $number)
                {
                    $diff["number_add"][$clientId][$number] = $statNums[$number];
                }

            if ($delNums)
                $diff["number_del"][$clientId] = $delNums;
        }

        // появлись номера у клиента, у которого раньше небыло привязок
        if ($addCl)
            foreach ($addCl as $clientId)
            {
                $diff["client_add"][$clientId] = $stat[$clientId];
            }

        // снятие всех привязок с клиента
        if ($delCl)
            $diff["client_del"] = $delCl;

        return $diff;
    }

    /**
     * Приобразовывает изменения в запросы
     */
    private static function diffToQuery($diff)
    {
        $queries = array();
        if (isset ($diff["client_add"]) && $diff["client_add"])
        {
            foreach ($diff["client_add"] as $clientId => $numbers)
            {
                foreach ($numbers as $n7800 => $nLine)
                {
                    $queries[] = self::_addNumber($clientId, $n7800, $nLine);
                }
            }
        }

        if (isset ($diff["client_del"]) && $diff["client_del"]) 
        {
            foreach ($diff["client_del"] as $clientId)
            {
                $queries[] = self::_delNumber($clientId);
            }
        }

        if (isset ($diff["number_add"]) && $diff["number_add"])
        {
            foreach ($diff["number_add"] as $clientId => $numbers)
            {
                foreach ($numbers as $n7800 => $nLine)
                {
                    $queries[] = self::_addNumber($clientId, $n7800, $nLine);
                }
            }
        }

        if (isset ($diff["number_del"]) && $diff["number_del"])
        {
            foreach ($diff["number_del"] as $clientId => $numbers)
            {
                foreach ($numbers as $n7800)
                {
                    $queries[] = self::_delNumber($clientId, $n7800);
                }
            }
        }

        if (isset ($diff["number_update"]) && $diff["number_update"])
        {
            foreach ($diff["number_update"] as $clientId => $numbers)
            {
                foreach ($numbers as $n7800 => $nLine)
                {
                    $queries[] = self::_updateNumber($clientId, $n7800, $nLine);
                }
            }
        }

        return $queries;
    }

    /**
     * Запрос на добавление номера
     */
    private static function _addNumber($clientId, $n7800, $nLine)
    {
        return array(
                "insert", 
                array(
                    "client_id" => $clientId, 
                    "number7800_id" => self::getNumberId($clientId, $n7800), 
                    "line_nonum_id" => self::getNumberId($clientId, $nLine)
                    )
                );
    }

    /**
     * Запрос на удаление номера
     */
    private static function _delNumber($clientId, $n7800 = null)
    {
        $sql = array("delete", array("client_id" => $clientId));

        if ($n7800 !== null)
        {
            $sql[1]["number7800_id"] = self::getNumberId($clientId, $n7800);
        }

        return $sql;
    }

    /**
     * Запрос на измнение номера
     */
    private static function _updateNumber($clientId, $n7800, $nLine)
    {
        return array(
                "update",
                array(
                    "client_id" => $clientId,
                    "number7800_id" => self::getNumberId($clientId, $n7800), 
                    "line_nonum_id" => self::getNumberId($clientId, $nLine)
                    ), 
                array("client_id", "number7800_id")
                );
    }

    /**
     * Получаем ID номера
     *
     * @param int $clientId id аккаунта
     * @param int $number телефонный номер
     */
    private static function getNumberId($clientId, $number)
    {
        global $db_ats;

        return $db_ats->GetValue("select id from a_number where client_id='".$clientId."' and number = '".$number."'");
    }

    /**
     * Рализация запросов в базу
     */
    private static function execQueryies($queries)
    {
        global $db_ats;

        $table = "a_7800_line";

        foreach ($queries as $data)
        {
            $fn = $data[0];
            $fields = $data[1];
            if ( $fn == "insert" )
            {
                $db_ats->QueryInsert($table, $fields);
            } elseif ( $fn == "delete" )
            {
                $db_ats->QueryDelete($table, $fields);
            } elseif ( $fn == "update" )
            {
                $keys = $data[2];
                $db_ats->QueryUpdate($table, $keys, $fields);
            }
        }
    }



}

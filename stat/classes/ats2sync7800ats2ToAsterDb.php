<?php

/**
 * Класс синхронизации привязанных линий без номера к номерам 7800, из ats2 в базу астриска
 *
 * @author Andreev Dmitriy
 */

class ats2sync7800ats2ToAsterDb
{
    private static $loaded = array();
    private static $saved = array();
    private static $diff = array();

    /**
     * Запсуск синхронизации
     */
    public static function sync()
    {
        self::load();
        self::diff();

        if (self::$diff) 
        {
            self::applyDiff();
        }
    }

    /**
     * Загрузка данных
     */
    private static function load()
    {
        global $db_ats, $pDB;

        if ($pDB === null)
        {
            $pDB = self::_connect();
        }

        foreach ($db_ats->AllRecords(
            "SELECT
            ifnull(a7800.number, 0) as number7800,
                ifnull(anonum.number, 0) as line_nonum, anonum.region
                FROM
                a_7800_line ln
                LEFT JOIN a_number a7800 ON (ln.number7800_id = a7800.id and ln.client_id = a7800.client_id)
                LEFT JOIN a_number anonum ON (ln.line_nonum_id = anonum.id and ln.client_id = anonum.client_id)
            HAVING number7800 != 0 and line_nonum != 0
                ") as $l)
        {
            self::$loaded[$l["number7800"]] = array("line" => $l["line_nonum"], "region" => $l["region"]);
        }


        foreach ($pDB->AllRecords("SELECT * FROM ".PG_SCHEMA.".number_location") as $l)
        {
            self::$saved[$l["number"]] = array("line" => $l["fake_number"], "region" => $l["number_region"]);
        }
    }

    /**
     * Сравнение загруженных данных
     */
    private static function diff()
    {
        self::$diff = array();

        $k1 = array_keys(self::$loaded);
        $k2 = array_keys(self::$saved);

        //add
        $add = array_diff($k1, $k2);
        if ($add)
        {
            foreach ($add as $l)
            {
                self::$diff["add"][$l] = self::$loaded[$l];
            }
        }

        //del
        $del = array_diff($k2, $k1);
        if ($del)
        {
            self::$diff["del"] = $del;
        }


        foreach (array_intersect($k1, $k2) as $number)
        {
            if (self::$loaded[$number]["line"] != self::$saved[$number]["line"])
            {
                self::$diff["change"][$number]["line"] = self::$loaded[$number]["line"];
            }

            if (self::$loaded[$number]["region"] != self::$saved[$number]["region"])
            {
                self::$diff["change"][$number]["region"]= self::$loaded[$number]["region"];
            }
        }
    }

    /**
     * Применение изменений
     */
    private static function applyDiff()
    {
        global $pDB;

        // добавление привязки
        if (isset(self::$diff["add"]))
        {
            foreach(self::$diff["add"] as $number => $line)
            {
                $data = array(
                    "number" => $number,
                    "fake_number" => $line["line"],
                    "number_region" => $line["region"]
                );

                $pDB->QueryInsert(PG_SCHEMA.".number_location", $data);
            }
        }

        // удаление привязки
        if (isset(self::$diff["del"]))
        {
            foreach (self::$diff["del"] as $number)
            {
                $pDB->QueryDelete(PG_SCHEMA.".number_location", array("number" => $number));
            }
        }

        // изменение привязки
        if (isset(self::$diff["change"]))
        {
            foreach (self::$diff["change"] as $number => $d)
            {
                $data = array("number" => $number);

                if (isset($d["line"]))   $data["fake_number"]   = $d["line"];
                if (isset($d["region"])) $data["number_region"] = $d["region"];

                $pDB->QueryUpdate(PG_SCHEMA.".number_location", "number", $data);
            }
        }
    }

    private static function _connect()
    {
        $pDB = new PgSQLDatabase(PG_ATS_HOST, PG_ATS_USER, PG_ATS_PASS, PG_ATS_DB);
        $pDB->Connect() or die("PgSQLDatabase not connected");

        define("PG_SCHEMA", "astschema");

        return $pDB;
    }
}


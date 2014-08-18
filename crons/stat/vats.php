<?php 
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";

echo "\n".date("r")."\n\n";

$argvCount = count($_SERVER["argv"]);

if ($argvCount < 2)
{
    echoHelp();
    exit();
} else {
    if ($argvCount == 2)
    {
        if (!in_array($_SERVER["argv"][1], array("today", "yesterday", "month", "prevmonth")))
        {
            echo "\n***Неправильно заданы параметры***\n\n";
            echoHelp();
            exit();
        } 
    }
}

function echoHelp()
{
    echo "\nСкрипт сбора статистики по ВАТС.";
    echo "\n";
    echo "\n";
    echo "\nПараметры запуска:";
    echo "\n";
    echo "\n".$_SERVER["PHP_SELF"]." today       -- получит и сохранит статистику за сегодня.";
    echo "\n".$_SERVER["PHP_SELF"]." yesterday   -- получит и сохранит статистику за вчера.";
    echo "\n".$_SERVER["PHP_SELF"]." month       -- получит и сохранит статистику с первого дня текущего месяца, по сегодня.";
    echo "\n".$_SERVER["PHP_SELF"]." prevmonth   -- получит и сохранит статистику с первого дня предыдущего месяца, по сегодня.";
    echo "\n";
    echo "\n";
}

switch ($_SERVER["argv"][1])
{
    case 'today':     $startDate = time(); break;
    case 'yesterday': $startDate = strtotime("-1 day"); break;
    case 'month':     $startDate = strtotime("first day of this month"); break;
    case 'prevmonth': $startDate = strtotime("first day of previous month"); break;
    default: 
                      echo "\n***Неправильно заданы параметры***\n\n";
                      echoHelp();
                      exit();
}

$startDate = strtotime("00:00:00", $startDate);

if ($_SERVER["argv"][1] == "yesterday")
{
    $endDate = strtotime("-1 day 00:00:00");
} else {
    $endDate = strtotime("00:00:00");
}


foreach ($db_ats->AllRecords("SELECT client_id FROM `a_virtpbx` where is_started = 'yes'") as $vats)
{
    $clientId = $vats["client_id"];

    echo "\n--------------------------\nclientId: ".$clientId;

    for($d = $startDate; $d <= $endDate; $d = strtotime("+1 day", $d))
    {
        $day = date("Y-m-d", $d);

        $vatsStat = new VpbxStatisticProcessor($clientId, $day);

        try{
            $stat = $vatsStat->getStatistic();

            if ($stat["space"] !== null || $stat["numbers"] !== null)
            {
                echo "\n".date("Y-m-d", $d).": ".$stat["space"]." || ".$stat["numbers"];
            }

        } catch (Exception $exc)
        {
            echo "*******Error: ".$exc->getCode().": ".Encoding::toUTF8($exc->getMessage());
            break;
        }


        $vatsStat->save();
    }
}


/**
* Класс получения и сохранения статистики
*/
class VpbxStatisticProcessor
{
    private $clientId = 0;
    private $day = null;
    private $_stat = null;

    public function __construct($clientId, $day)
    {
        $this->clientId = $clientId;
        $this->day = $day;
    }

    /**
    * Функция получения статистики
    */
    public function getStatistic()
    {
        return $this->_stat = array(
                "space"   => $this->getStatisticValue("getUsageSpaceStatistic"),
                "numbers" => $this->getStatisticValue("getUsageNumbersStatistic")
                );
    }

    /**
    * Сохранение ранее полученной статситики в базу
    */
    public function save()
    {
        if ($this->_stat === null)
            throw new Exception("Статистика не получена");

        VirtpbxStat::table()->delete(array("client_id" => $this->clientId, "date" => $this->day));

        $stat = $this->_stat;

        if ($stat["space"] || $stat["numbers"])
        {
            $record = new VirtpbxStat();

            $record->date = $this->day;
            $record->client_id = $this->clientId;

            if ($stat["space"])
                $record->use_space = $stat["space"];

            if ($stat["numbers"])
                $record->numbers = $stat["numbers"];

            $record->save();
        }
    }

    /**
    * Получение параметра отдельного параметра статситики
    */
    private function getStatisticValue($fn)
    {
        $v = null;
        try{
            $v = ApiVpbx::$fn($this->clientId, $this->day);
        } catch(Exception $e)
        {
            if ($e->getCode() != 540)
            {
                throw $e;
            }
        }

        return $v;
    }
}

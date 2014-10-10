<?php


class Schema
{
    public function Schema(&$modObj, $fixclient)
    {
        //router

        $clientId = $this->resolveClientId($fixclient);

        if(get_param_raw("make","") == "schedule")
        {
            $this->makeSchedule();
        }

        if(($del = get_param_raw("del", false)) !== false)
        {
            $this->delSchema($clientId, $del);
        }

        if(($id = get_param_raw("id", false)) !== false)
        {
            if($id == 0)
            {
                $this->addSchema($fixclient, $clientId, $id);
            }else{
                if(get_param_raw("do", "") == "save")
                {
                    $this->saveShema($fixclient, $clientId, $id);
                    $modObj->generateClient($fixclient);
                    exit();
                }
                $this->editSchema($fixclient, $clientId, $id);
            }
        }else{
            $this->listSchema($clientId);
        }
    }

    private function resolveClientId($fixclient)
    {
        global $db;
        return $db->GetValue("select id from ".SQL_DB.".clients where id = '".$fixclient."' or client = '".$fixclient."'");
    }


    private function listSchema($clientId)
    {
        global $db, $design;

        $design->assign("list", $db->AllRecords("SELECT id, name FROM r_schema where client_id = '".$clientId."' ORDER BY name"));
        $design->AddMain("ats/schema_list.htm");
    }

    private function delSchema($clientId, $id)
    {
        global $db;

        $db->Query("delete from r_timeblock where schema_id = '".$id."'");
        $db->Query("delete from r_time where schema_id = '".$id."'");
        $db->Query("delete from r_action where schema_id = '".$id."'");
        $db->Query("delete from r_number where schema_id = '".$id."'");
        $db->Query("delete from r_schema where id = '".$id."'");
    }

    private function makeSchedule()
    {
        $d = $_POST["data"];

        $d = simplexml_load_string($d);

        $counter = 0;
        $times = array();
        foreach($d->day_id as $k => $l)
        {
            $times[] = array("day_id" => (string)$l,"from" => (string)$d->from[$counter], "until" => (string)$d->until[$counter]);
            $counter++;
        }
        echo helper_schedule::timesToText($times);
        echo helper_schedule::convertTimesToInputs($times, rand(1,100000), false);
        exit();
    }

    private function saveShema($fixclient, $clientId, $id)
    {
        global $db;
        include "schema_save.php";
    }

    private function addSchema($fixclient, $clientId, $id)
    {
        global $db, $design;
        $name = get_param_raw("schema", "");
        $error = "";
        if(get_param_raw("do","") == "save")
        {
            try{
                if(!$name) 
                    throw new Exception("Имя не задано");
                if($db->GetRow("select * from r_schema where name ='".mysql_escape_string($name)."' and client_id = '".$clientId."'"))
                {
                    throw new Exception("Схема с таким именем уже существует");
                }
                
            }catch(Exception $e)
            {
                $error = $e->getMessage();
            }

            if(!$error)
            {
                $schemaId = $db->QueryInsert("r_schema", array("client_id" => $clientId, "name" => $name));
                $db->QueryInsert("r_timeblock", array("schema_id" => $schemaId, "is_alltime" => "yes"));
                header("Location: ./?module=ats&action=schema&id=".$schemaId);
                exit();
            }

        }
        $design->AddMain("ats/schema_add.htm");
        $design->assign("schemaName", $name);
        $design->assign("error", $error);
    }

    private function editSchema($fixclient, $clientId, $id)
    {
        include "schema_loader.php";

        $aStatus = array("" => "", "busy" => "Занят", "not_avail" => "Недоступен","not_answer" => "Нет ответа");

        global $db, $design;
        $schemaId = $id;
        $d = array();

        $soundFiles = $db->AllRecords("select id, name from anonses where ".sqlClient(), "id");
        $schemaName = $db->GetValue("select name from r_schema where id = '".$schemaId."'");

        $d = schemaLoader::load($schemaId, $soundFiles, $aStatus);

        $nullTimeBlock = 
            array(
                    "id" => 0, 
                    "is_alltime" => true,
                    "title" => "...",
                    "status" => "alltime",
                    "times" => array(
                        // nothing
                        ),
                    "redirs" => array(
                        array("action" => "hangup", "timeout" => 0, "status" => "","sound_id" =>0,"strategy" => "ringall")
                        )
                 );
        $design->assign("nullTimeBlock", json_encode($nullTimeBlock));
        $design->assign("rData", json_encode($d));
        $design->assign("schemaName", $schemaName);
        $design->assign("schemaId", $id);
        $design->assign("soundFiles", $soundFiles);
        $design->AddMain("ats/schema.htm");
    }
}

class helper_schedule
{
    public function timesToText($v)
    {
        $arrDays = array(
                "8" => array("title" => "Все дни", "smalltitle" => "Все дни"),
                "1" => array("title" => "Понедельник", "smalltitle" => "ПН"),
                "2" => array("title" => "Вторник", "smalltitle" => "ВТ"),
                "3" => array("title" => "Среда", "smalltitle" => "СР"),
                "4" => array("title" => "Четверг", "smalltitle" => "ЧТ"),
                "5" => array("title" => "Пятница", "smalltitle" => "ПТ"),
                "6" => array("title" => "Суббота", "smalltitle" => "СБ"),
                "7" => array("title" => "Воскресенье", "smalltitle" => "ВС"),
                "9" => array("title" => "Рабочие дни", "smalltitle" => "Рабочие дни"),
                "10" => array("title" => "Выходные", "smalltitle" => "Выходные")
                );

        $cc = self::compactTimes($v);
        $s = "";

        foreach($cc as $time => $c)
        {
            $ts = "";
            foreach($c as $dayId => $devNull)
            {
                $ts .= ($ts ? ", ": "").$arrDays[$dayId]["smalltitle"];
            }

            $s .= ($s ? "; ": "").$ts.": ".$time;
        }

        return $s;
    }

    private function compactTimes($v)
    {
        $d = array();
        foreach($v as $l)
        {
            $hash = $l["from"]."-".$l["until"];
            if(!isset($d[$hash]))
            {
                $d[$hash] = array();
            }
            $d[$hash][$l["day_id"]] = 1;
        }
        return $d;

    }

    public function convertTimesToInputs($v, $id, $isAllTime)
    {
        $inputs = "<input type=hidden name=time_".$id."[time][is_alltime] value=".$isAllTime.">\n";

        foreach($v as $k => $t)
        {
            $inputs .= "<input type=hidden name=time_".$id."[".$k."][day_id] value='".$t["day_id"]."'>\n";
            $inputs .= "<input type=hidden name=time_".$id."[".$k."][from] value='".$t["from"]."'>\n";
            $inputs .= "<input type=hidden name=time_".$id."[".$k."][until] value='".$t["until"]."'>\n";
        }
        return $inputs;
    }
}

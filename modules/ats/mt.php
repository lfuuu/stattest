<?php


class MT
{
    function __construct($fixclient, $action, $id = 0)
    {
        switch($action)
        {
            case 'view': $this->view(); break;
            case 'add': $this->edit(0); break;
            case 'edit': $this->edit($id); break;
        }
    }

    function view()
    {
        global $design, $db;;
        $this->checkViewActions();

        $m = array();
        $p = array();
        $v = array();

        foreach($db->AllRecords("
                    select id,atype, number, client_id, parent_id, call_count, is_pool 
                    from v_sip where type='multitrunk' order by number") as $l)
        {
            $l["client"] = getClientById($l["client_id"]);
            $m[$l["id"]] = $l;
            if(!isset($p[$l["parent_id"]])) $p[$l["parent_id"]] = array();
            $p[$l["parent_id"]][] = $l["id"];
        }

        if(isset($p["0"]))
        {
            foreach($p["0"] as $l)
            {
                if(isset($p[$l]))
                    foreach($p[$l] as $ll)
                    {
                        $m[$ll]["numbers"] = $db->AllRecords(
                                "select number,call_count, direction 
                                from v_number_mt m, v_number n 
                                where m.number_id = n.id and sip_id =".$ll);

                        $m[$l]["parent"][] = $m[$ll];
                    }
                $v[] = $m[$l];
            }
        }

        $design->assign("mts", $v);//$db->AllRecords("Select id, number from v_sip where client = '' order by number"));

        $design->AddMain("ats/mt.htm");
    }

    function checkViewActions()
    {
        global $db;

        if(get_param_raw("subaction", "") == "delete")
        {
            foreach(get_param_raw("ids", array()) as $ll)
            {
                foreach($db->AllRecords("select id from v_sip where '".$ll."' in (parent_id, id) and type ='multitrunk'") as $l)
                {
                    $db->QueryDelete("v_sip", array(
                                "id" => $l["id"],
                                )
                            );

                    $db->QueryDelete("v_number_mt", array(
                                "sip_id" => $l["id"],
                                )
                            );

                }
            }
        }
    }

    function edit($id)
    {

        if(get_param_raw("cancel", "") != "") {
            header("Location: ./?module=ats&action=mt");
            exit();
        }

        global $db, $design, $fixclient;

        $isSaved = get_param_raw("saved", "") == "ok";
        if($id == 0)
        {
            $data = array(
                    "id" => 0,
                    "number" => "",
                    "client" => $fixclient,
                    "password" => "",
                    "host_type" => "dynamic",
                    "host_static" => "",
                    "dtmf" => "rfc2833",
                    "permit_on" => "auto",
                    "permit" => "",
                    "codec" => "alaw,g729",
                    "context" => "c-trunk-out"
                    );
        }else{
            $data = $db->GetRow("select * from v_sip where id ='".$id."' and type='multitrunk' and atype='multitrunk'");
            $data["client"] = getClientById($data["client_id"]);
            unset($data["client_id"]);
        }

        $this->countInPool($id);

        $map = $this->getMap();

        include_once INCLUDE_PATH."formconstructor.php";
        $formConstr = new FormConctructor($map);

        $error = "";

        if(get_param_raw("do", ""))
        {
            $gData = $formConstr->gatherFromData($id);

            $gData["id"] = $id;
            
            $this->prepare($gData);

            try
            {
                $this->check($gData, $id);
            }catch(Exception $e) {
                $error = $e->GetMessage();;
            }

            if($error)
            {
                $formConstr->make($gData);
                $data = $gData;
            }else{

                if($isSaved = $this->apply($gData))
                {
                    header("Location: ./?module=ats&action=mt_edit&id=".$isSaved."&saved=ok");
                }else{
                    header("Location: ./?module=ats&action=mt");
                }

                exit();
            }
        }else{
            $formConstr->make($data);
        }

        $design->assign("error", $error);
        $design->assign("isSaved", $isSaved);
        $design->assign("data", $data);

        $design->AddMain("ats/mt_add.htm");
    }

    private function apply(&$data)
    {
        global $db;

        $data["client_id"] = $db->GetValue("select id from ".SQL_DB.".clients where client='".$data["client"]."'");
        unset($data["client"]);

        if($data["id"] == 0)
        {
            unset($data["id"]);
            $data["type"] = "multitrunk";
            $data["atype"] = "multitrunk";

            return $db->QueryInsert("v_sip", $data);
        }else{
            $n = $db->GetRow("select * from v_sip where id = '".$data["id"]."'");
            if($n)
            {
                $db->QueryUpdate("v_sip", "id", $data);
                if($n["number"] != $data["number"])
                {
                    $db->QueryUpdate("v_sip", "parent_id", array(
                                "number" => $data["number"],
                                "parent_id" => $n["id"]
                                )
                            );
                }
            }
            return /*$db->AffectedRows() ? $data["id"] :*/ 0;
        }
    }

    private function countInPool($id)
    {
        global $design, $db;

        if($id == 0) 
        {
            $c = 0;
        }else{
            $c = $db->GetValue(
                    "select sum(n.call_count) 
                    from v_sip s, v_number_mt m, v_number n  
                    where s.parent_id = ".$id." and m.sip_id = s.id and n.id = m.number_id");                        
        }
        $design->assign("count_in_pool", $c);
    }

    private function getMap()
    {
        global $db, $user, $fixclient;

        $codecs = array("alaw" => "alaw", "g729" => "g729", "gsm" => "GSM", "ulaw" => "ulaw");
        $dcYesNo = array("type" => "array", "array" => array("yes" => ""));

        $m = array();

        $m["number"] = array(
                "title" => "Название транка",
                "type" => "text",
                );

        $m["client"] = array(
                "title" => "Клиент",
                "type" => "text",
                );


        $m["host_type"] = array(
                "title" => "Host",
                "type" => "radio",
                "data" => array(array("type" => "array", "array" => array("static" => "static","dynamic" => "dynamic"))),
                "default" => "dynamic"
                );

        $m["host_static"] = array(
                "title" => "Host IP",
                "type" => "text",
                "condition" => array("eq", "host_type", "static")
                );

        $m["password"] =array(
                "title" => "Пароль",
                "type" => "text",
                "condition" => array("nq", "host_type", "static")
                );

        $m["dtmf"] = array(
                "title" => "DTMF",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array(
                            "rfc2833" => "rfc2833","inband" => "inband","info" => "info"
                            )))
                );


        $m["permit_on"] = array(
                "title" => "Привязка",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array("yes" => "Да", "no" => "Нет", "auto" => "Авто"))),
                "condition" => array("nq", "host_type", "static")
                );

        $m["permit"] = array(
                "title" => "Привязка к IP",
                "type" => "permit_net",
                "condition" => array("and", array("eq", "permit_on", "yes"),array("nq", "host_type", "static"))
                );

        $m["break"] = array(
                "type" => "break"
                );

        $m["codec"] = array(
                "title" => "Кодеки",
                "type" => "sort_list",
                "data_all" => array(array("type" => "array", "array" => $codecs)),
                );

        $m["context"] = array(
                "title" => "Контекст",
                "type" => "text"
                );

        $m["is_pool"] = array(
                "title" => "Объединить счетчик звонков",
                "type" => "is_pool",
                "data" => array($dcYesNo)
                );

        return $m;
    }

    private function prepare(&$d)
    {
        if(!$d["is_pool"]) $d["is_pool"] = "no";
        if($d["host_type"] == "static") $d["password"] = "";
    }

    private function check(&$data, $id)
    {
        global $db;

        include INCLUDE_PATH."checker.php";

        checker::isEmpty($data["number"], "Название транка не задано!");
        checker::isEmpty($data["client"], "Клиент не задан!");

        if(!$db->GetValue("select id from `".SQL_DB."`.clients where client='".mysql_escape_string($data["client"])."'"))
            throw new Exception("Клиент не найден!");

        if($data["host_type"] != "static")
            checker::isEmpty($data["password"], "Пароль не задан!");

        if($db->GetValue("select id from v_sip where number = '".$data["number"]."' and type='multitrunk' and atype = 'multitrunk' and id != '".$id."'"))
            throw new Exception("Транк с таким названием уже используется");

        if($data["host_type"] == "static")
        {
            checker::isEmpty($data["host_static"], "Не задан статический host!");
            checker::isValideIp($data["host_static"], "Статический host задан не верно!");

            if($id == 0 && $data["permit_on"] == "yes")
            {
                if(strpos($data["permit"], $data["host_static"]) === false)
                {
                    $data["permit"] .= ($data["permit"] ? "," : "").$data["host_static"]."/32";
                }
            }else{
                //checker::isEmpty($data["permit"], "Если задан статический Host IP, необходимо сделать привязку");
            }
        }

        if($data["permit_on"] == "yes" && $data["host_type"] == "static")
        {
            checker::isEmpty($data["permit"], "Привязка включена, но не задана!");
        }

        checker::isEmpty($data["codec"], "Задайте кодеки!");
        checker::isEmpty($data["context"], "Контекст не задан!");
    }
}


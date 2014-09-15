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
        global $design, $db_ats;

        $this->checkViewActions();

        $m = array();
        $p = array();
        $v = array();


        /*
        foreach($db_ats->AllRecords("
                    select id, number, client_id, parent_id, call_count, is_pool 
                    from a_multitrunk where type='multitrunk' order by number") as $l)
                    */

        foreach($db_ats->AllRecords("
                    select id, name, client_id, parent_id, call_count, is_pool 
                    from a_multitrunk order by name") as $l)
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
                        $m[$ll]["numbers"] = $db_ats->AllRecords(
                                "select number,call_count, direction 
                                from a_link m, a_number n 
                                where m.number_id = n.id 
                                and c_id =".$ll." and c_type='multitrunk' order by m.id");

                        $m[$l]["parent"][] = $m[$ll];
                    }
                $v[] = $m[$l];
            }
        }

        $design->assign("mts", $v);//$db_ats->AllRecords("Select id, number from a_multitrunk where client = '' order by number"));

        $design->AddMain("ats2/multitrunk_list.htm");
    }

    function checkViewActions()
    {
        global $db_ats;

        if(get_param_raw("subaction", "") == "delete_link")
        {
            $clientsForUpdate = array();
            foreach(get_param_raw("ids", array()) as $ll)
            {
                foreach($db_ats->AllRecords(
                            "select id, client_id ,
                                (select client_id from a_multitrunk m1 where m1.id=m.parent_id) as trunk_client_id
                            from a_multitrunk m
                            where 
                                    id in ('".$ll."') 
                                and parent_id != 0") as $l)
                {
                    $db_ats->QueryDelete("a_multitrunk", array(
                                "id" => $l["id"],
                                )
                            );

                    $db_ats->QueryDelete("a_link", array(
                                "c_id" => $l["id"],
                                "c_type" => "multitrunk"
                                ));

                    $clientsForUpdate[$l["client_id"]] =1;
                    $clientsForUpdate[$l["trunk_client_id"]] =1;
                }
            }

            if($clientsForUpdate)
                ats2sync::updateClient(array_keys($clientsForUpdate));
        }

        if(get_param_raw("subaction", "") == "delete_trunk")
        {
            $clientsForUpdate = array();
            foreach(get_param_raw("ids", array()) as $ll)
            {
                foreach($db_ats->AllRecords("select id, c_id, client_id 
                            from a_multitrunk 
                            where id in ('".$ll."') and parent_id = 0") as $l)
                {

                    foreach($db_ats->AllRecords("select id, client_id 
                                from a_multitrunk 
                                where parent_id = '".$l["id"]."' and parent_id != 0") as $k)
                    {
                        $db_ats->QueryDelete("a_multitrunk", array(
                                    "id" => $k["id"],
                                    )
                                );

                        $db_ats->QueryDelete("a_link", array(
                                    "c_id" => $k["id"],
                                    "c_type" => "multitrunk"
                                    ));
                        $clientsForUpdate[$k["client_id"]] = 1;
                    }

                    $db_ats->QueryDelete("a_multitrunk", array(
                                "id" => $l["id"],
                                )
                            );

                    $db_ats->QueryDelete("a_connect", array(
                                "id" => $l["c_id"],
                                )
                            );
                        $clientsForUpdate[$l["client_id"]] = 1;
                }
            }
            if($clientsForUpdate)
                ats2sync::updateClient(array_keys($clientsForUpdate));
        }
    }

    function edit($id)
    {

        if(get_param_raw("cancel", "") != "") {
            header("Location: ./?module=ats2&action=mt");
            exit();
        }

        global $design;

        $isSaved = get_param_raw("saved", "") == "ok";

        $data = mtDB::get($id);


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
                $this->apply($gData);

                ats2sync::updateClient($gData["client_id"]);

                header("Location: ./?module=ats2&action=mt");
                exit();
            }
        }else{
            if($id != 0 && $data["password"])
                $data["password"] = "********";

            $data["key"] = ats2passcrypt::cryptId($data["id"]);

            $formConstr->make($data);
        }

        
        $design->assign("count_in_pool", ats2mt::countInPool($id));

        $design->assign("error", $error);
        $design->assign("isSaved", $isSaved);
        $design->assign("data", $data);

        $design->AddMain("ats2/multitrunk_edit.htm");
    }

    private function apply(&$data)
    {
        if($data["password"] == "********") // не изменяем пароль, если он не поменялся
        {
            if($data["id"] == 0)
            {
                $data["password"] = password_gen();
            }else{
                unset($data["password"]);
            }
        }else{
            $this->logPassword($data, "change");
        }

        $data["client_id"] = getClientId($data["client"]);
        unset($data["client"]);

        if($data["id"] == 0)
        {
            unset($data["id"]);

            return mtDB::insert($data);
        }else{
            mtDB::update($data);
        }
    }

    public function logPassword($s, $event = "view")
    {
            global $user, $db_ats;

            $client = getClient($s["client"]);
            $db_ats->QueryInsert("a_log_password",array(
                        "client_id" => $client["id"],
                        "time" => array("NOW()"),
                        "account" => $s["name"],
                        "c_id" => $s["id"],
                        "c_type" => "multitrunk",
                        "user_id" => $user->Get("id"),
                        "event" => $event
                        )
                    );
    }
    private function getMap()
    {
        $codecs = array("alaw" => "alaw", "g729" => "g729", "gsm" => "GSM", "ulaw" => "ulaw");
        $dcYesNo = array("type" => "array", "array" => array("yes" => ""));

        $m = array();

        $m["name"] = array(
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
                "default" => "dynamic",
                "onchange" => "check_if_static()"
                );

        $m["host_static"] = array(
                "title" => "Host IP",
                "type" => "text",
                "condition" => array("eq", "host_type", "static")
                );
        $m["host_port_static"] = array(
                "title" => "Port",
                "type" => "text",
                "condition" => array("eq", "host_type", "static")
                );

        $m["password"] =array(
                "title" => "Пароль",
                "type" => "password_ats2"
                );

        $m["dtmf"] = array(
                "title" => "DTMF",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array(
                            "rfc2833" => "rfc2833",
                            "inband" => "inband",
                            "info" => "info"
                            )))
                );

        $m["insecure"] = array(
                "title" => "Авторизация",
                "hint" => "опция: insecure",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array(
                            "" => "Полная",
                            "invite" => "По IP и порту",
                            "invite,port" => "Только по IP"
                            )))
                );

        $m["permit_on"] = array(
                "title" => "Привязка",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array("yes" => "Да", "no" => "Нет", "auto" => "Авто")))
                );

        $m["permit"] = array(
                "title" => "Привязка к IP",
                "type" => "permit_net",
                "condition" => array("eq", "permit_on", "yes")
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
    }

    private function check(&$data, $id)
    {
        global $db_ats;

        include INCLUDE_PATH."checker.php";

        checker::isEmpty($data["name"], "Название транка не задано!");
        checker::isEmpty($data["client"], "Клиент не задан!");

        checker::isEmpty(getClient($data["client"]), "Клиент не найден!");

        if($data["host_type"] != "static")
            checker::isEmpty($data["password"], "Пароль не задан!");

        if($db_ats->GetValue("select id from a_multitrunk where name = '".$data["name"]."' and parent_id = 0 and id != '".$id."'"))
            throw new Exception("Транк с таким названием уже используется");

        if($data["host_type"] == "static")
        {
            checker::isEmpty($data["host_static"], "Не задан статический host!");
            checker::isValideIp($data["host_static"], "Статический host задан не верно!");

            checker::isDigits($data["host_port_static"], "Статический порт задан не верно!");
            checker::number_isBetween($data["host_port_static"], 1, 65536, "Статический порт задан не верно!");

            if($id == 0 && $data["permit_on"] == "yes")
            {
                if(strpos($data["permit"], $data["host_static"]) === false)
                    $data["permit"] .= ($data["permit"] ? "," : "").$data["host_static"]."/32";
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

class mtDB
{
    public function get($id)
    {
        if($id == 0)
        {
            $c = getClient();
            $data = array(
                    "id" => 0,
                    "name" => "",
                    "client" => $c["client"],
                    "password" => "********",
                    "host_type" => "dynamic",
                    "host_static" => "",
                    "host_port_static" => "5060",
                    "dtmf" => "rfc2833",
                    "permit_on" => "auto",
                    "permit" => "",
                    "codec" => "alaw,g729",
                    "context" => "c-multitrunk-out",
                    "is_pool" => 0
                    );
        }else{

            global $db_ats;

            $data = $db_ats->GetRow("select * from a_multitrunk m, a_connect c where c.id = m.c_id and m.id = ".$id);
            $data["id"] = $id;
            $data["client"] = getClientById($data["client_id"]);
            unset($data["client_id"]);

        }
        $data["is_pool"] = $data["is_pool"] ? "yes" : "no";

        return $data;
    }

    public function insert($d)
    {
        global $db_ats;
        /*
           =Array
           (
           [name] => tr1
           [host_type] => dynamic
           [host_static] => 
           [password] => pass
           [dtmf] => rfc2833
           [permit_on] => auto
           [permit] => 
           [codec] => alaw,g729
           [context] => c-trunk-out
           [is_pool] => no
           [client_id] => 9130
           )

           +------------+
           | Field      |
           +------------+
           | id         |
           | client_id  |
           | name       |
           | parent_id  |
           | c_id       |
           | call_count |
           | is_pool    |
           +------------+

         */

        $d["is_pool"] = $d["is_pool"] == "yes" ? 1 : 0;

        $am = array();
        foreach(array("client_id", "name", "is_pool") as $f)
        {
            $am[$f] = $d[$f];
            unset($d[$f]);
        }

        $am["c_id"] = $db_ats->QueryInsert("a_connect", $d, true);
        $id = $db_ats->QueryInsert("a_multitrunk", $am, true);

        return $id;
    }

    public function update($d)
    {
        global $db_ats;

        $n = self::get($d["id"]);

        $id = $n["id"];
        $cId = $n["c_id"];

        $dn = array_diff_assoc($d, $n);
        unset($dn["client_id"]);

        if(isset($dn["is_pool"]))
            $dn["is_pool"] = $dn["is_pool"] == "yes" ? 1 : 0;

        if($dn)
        {

            $_dn = $dn;
            unset($_dn["is_pool"]);
            unset($_dn["name"]);

            if($_dn)
            {
                $_dn["id"] = $cId;
                $db_ats->QueryUpdate("a_connect", "id", $_dn);
            }

            if(isset($dn["name"]) || isset($dn["is_pool"]))
            {
                $update = array("id" => $n["id"]);

                if(isset($dn["name"]))
                    $update["name"] = $dn["name"];

                if(isset($dn["is_pool"]))
                    $update["is_pool"] = $dn["is_pool"];

                $db_ats->QueryUpdate("a_multitrunk", "id", $update);
                $update["parent_id"] = $update["id"]; unset($update["id"]);
                $db_ats->QueryUpdate("a_multitrunk", "parent_id", $update);
            }
        }

        return /*$db_ats->AffectedRows() ? $data["id"] :*/ 0;
    }
}


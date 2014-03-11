<?php

define("exception_sql", 1);

function getClient($fixClient = null )
{

    global $db;

    if($fixClient == null)
    {
        if(isset($_SESSION["clients_client"]) && $_SESSION["clients_client"])
            $fixClient = $_SESSION["clients_client"];
    }

    if($fixClient === null)
        return "true = false";

    static $cach = array();
    if(!isset($cach[$fixClient]))
    {
        $cach[$fixClient] = $db->GetRow("select * from clients where client = '".mysql_escape_string($fixClient)."'");
    }

    return $cach[$fixClient];
    //
}
    function getClientById($id)
    {
        global $db;
        static $c = array();
        if(!isset($c[$id]))
            $c[$id] = $db->GetValue("select client from clients where id = '".$id."'");

        return $c[$id];
    }

function getClientId($fixClient = null)
{
    $r = getClient($fixClient);
    return $r ? $r["id"] : false;
}

function sqlClient($fixClient = null)
{
    $c = getClientId($fixClient);

    if(!$c) {
        die("Клиент не выбран");
        return "true = false";
    }
    return "client_id = ".$c;
}

class m_ats2 extends IModule
{

	function Install($p)
    {
		return $this->rights;
	}

	function GetMain($action,$fixclient){

		if (!isset($this->actions[$action])) return;

		$act=$this->actions[$action];

		if (!access($act[0],$act[1])) return;

        if(!$fixclient) {trigger_error("Клиент не выбран"); return;}

        try{
            /*
                if(!defined("print_sql"))
                    define("print_sql",1);

		if(!defined("save_sql"))
			define("save_sql", 1);

                    */

        define("save_sql", 1);

            call_user_func(array($this,'ats2_'.$action),$fixclient);

        }catch(Exception $e)
        {
            echo "<pre>";
            echo $e;
            echo "</pre>";
            exit();
        }
	}

    public function ats2_default($fixclient)
    {
        return $this->ats2_accounts($fixclient);
    }

    public function ats2_account_del($fixclient)
    {
        $id = get_param_integer("id", 0);

        if($id)
            lineDB::del($id);

        header("Location: ./?module=ats2");
        exit();
    }

    public function ats2_accounts($fixClient)
    {
        $this->account_list($fixClient);
        $this->number_list($fixClient);
        $this->ats2_log_view($fixClient);
        $this->update_key($fixClient);
        $this->virtpbx_list($fixClient);
    }

    private function number_list($fixClient)
    {
        global $db_ats, $design;

        $ns = array();
        foreach($db_ats->AllRecords($q = "
                    select 
                        n.id, number, k.c_id,
                        account, c_type, direction, n.enabled, 
                        m.name, m.parent_id as trunk_id,
                        (
                            select 
                                v.id 
                            from 
                                a_virtpbx_link l, a_virtpbx v 
                            where 
                                    v.id = l.virtpbx_id 
                                and v.client_id = '".getClientId()."' 
                                and type='number' 
                                and type_id = n.id
                        ) as is_virtpbx
                    from a_number n 
                    left join a_link k on (k.number_id = n.id) 
                    left join a_line l on (l.id = k.c_id and k.c_type != 'multitrunk') 
                    left join a_multitrunk m on (m.id = k.c_id and k.c_type = 'multitrunk')
                    where n.".sqlClient()." order by number, k.id") as $n)
        {
            if(!isset($ns[$n["number"]]))
                $ns[$n["number"]] = array(
                        "id" => $n["id"],
                        "number" => $n["number"], 
                        "type" => $n["c_type"], 
                        "c_id" => $n["c_id"], 
                        "trunk_id" => $n["trunk_id"], 
                        "name" => $n["name"], 
                        "is_virtpbx" => $n["is_virtpbx"],
                        "direction" => $n["direction"], 
                        "enabled" => $n["enabled"] == "yes",
                        "l"=>array()
                        );

            if($n["direction"])
                $ns[$n["number"]]["l"][] = array("id" => $n["c_id"], "account" => $n["account"]);

        }

        $design->assign("numbers", $ns);
        $design->AddMain("ats2/number_list.htm");
    }

    private function account_list($fixClient)
    {
        global $db_ats, $design;

        $d = array();
        $k = array();
        foreach($db_ats->AllRecords(
                    $q = "select 
                        s.id, parent_id, 
                        account, serial, sequence, is_group 
                    from a_line s, a_connect c
                    where c_id = c.id and ".sqlClient()." 
                    order by serial, is_group desc, sequence") as $l)
        {

            if($l["is_group"])
            {
                $k[$l["id"]] = count($d);
                $l["childs"] = array();
                $d[] = $l;
            }else{
                if(isset($k[$l["parent_id"]]))
                    $d[$k[$l["parent_id"]]]["childs"][] = $l;
            }
        }

        $design->assign("accounts", $d);
        $design->AddMain("ats2/account_list.htm");
    }

    public function ats2_account_add($fixClient)
    {
        $this->ats2_account($fixClient);
    }

    public function ats2_account($fixClient)
    {
        global $design;

        $id = get_param_integer("id", 0);
        $a = account::get($id);

        //printdbg($a);
        // cancel key
        if(get_param_raw("cancel", "") != "")
        {
            if($id == 0 && $a)
                reservAccount::reset();

            header("Location: ./?module=ats2");
            exit();
        }


        include_once INCLUDE_PATH."formconstructor.php";

        if(!$a){
            trigger_error("Аккаунт не найден");
            return;
        }

        $map = $this->getMap($a["is_group"]);

        if($a["is_group"] && $a["id"] > 0)
            $this->lookDiffInLines($map, $a);

        $formConstr = new FormConctructor($map);

        $error = false;

        if(get_param_raw("do", ""))
        {
            $gData = $formConstr->gatherFromData($id);

            $gData += account::parse($gData["account"]);

            $gData["id"] = $id;
            $gData["is_group"] = $a["is_group"];
            $gData["client_id"] = getClientId();

            try{
                $this->check($gData, $map);
            }catch(Exception $e)
            {
                $error = $e->getMessage();
            }


            if($error)
            {
                $formConstr->make($gData);
            }else{
                $id = $this->save($gData);

                header("Location: ./?module=ats2&actoin=account&id=".$id);
                exit();
            }
        }else{
            if($id != 0 && $a["password"])
                $a["password"] = "********";

            $a["key"] = ats2passcrypt::cryptId($a["id"]);

            $formConstr->make($a);
        }

        $design->assign("error", $error);
        $design->addMain("ats2/account_edit.htm");
    }

    private function save($d)
    {
        if($d["password"] == "********") // не изменяем пароль, если он не поменялся
        {
            if($d["id"] == 0)
            {
                $d["password"] = password_gen();
            }else{
                unset($d["password"]);
            }
        }else{
            $this->logPassword($d, "change");
        }

        if($d["id"] != 0)
        {
            $d["c_id"] = lineDB::getConnectId($d["id"]);
        }

        $id = $d["id"];

        if($d["id"] == 0) //add
        {
            $subAccountCount = $d["subaccount_count"];

            unset($d["id"], $d["subaccount_count"]);

            $id = lineDB::insert($d);

            reservAccount::reset();

            if($d["is_group"] && $subAccountCount > 0)
            {
                $d["id"] = $id;
                $d["subaccount_count"] = $subAccountCount;
                $this->checkForUpdate($d);
            }
        }else{

            if($changedTo = $this->checkForUpdate($d))
            {
                unset($changedTo["subaccount_count"]);

                if($changedTo)
                {
                    $changedTo["id"] = $d["id"];
                    lineDB::update($changedTo);
                }
            }
        }
        
        ats2sync::updateClient($d["client_id"]);

        return $id;
    }

    private function check(&$d, $map)
    {
        include INCLUDE_PATH."checker.php";

        if($d["id"] == 0)
            $this->check_account($d);

        if($d["host_type"] != "static")
            checker::isEmpty($d["password"], "Пароль не задан!");


        if(isset($map["subaccount_count"]))
        {
            checker::isDigits($d["subaccount_count"], "Количество линий задано не верно!");
            checker::number_isBetween($d["subaccount_count"], 0,99, "Количество линий задано не верно!");
        }

        if(isset($map["host_type"]))
            if($d["host_type"] == "static")
            {
                checker::isEmpty($d["host_static"], "Не задан статический host!");
                checker::isValideIp($d["host_static"], "Статический host задан не верно!");

                if(!$d["is_group"])
                {
                    checker::isDigits($d["host_port_static"], "Статический порт задан не верно!");
                    checker::number_isBetween($d["host_port_static"], 1, 65536, "Статический порт задан не верно!");
                }

                if($d["id"] == 0 && $d["permit_on"] == "yes")
                {
                    if(strpos($d["permit"], $d["host_static"]) === false)
                        $d["permit"] .= ($d["permit"] ? "," : "").$d["host_static"]."/32";
                }else{
                    //checker::isEmpty($data["permit"], "Если задан статический Host IP, необходимо сделать привязку");
                }
            }

        if(isset($map["permit_on"]))
            if($d["permit_on"] == "yes" && $d["host_type"] != "static")
                checker::isEmpty($d["permit"], "Привязка включена, но не задана!");

        checker::isEmpty($d["codec"], "Задайте кодеки!");

        checker::isEmpty($d["context"], "Контекст не задан!");
    }

    private function check_account(&$d)
    {
        global $db_ats;

        if($db_ats->GetValue("
                    select id 
                    from a_line
                    where 
                            serial='".$d["serial"]."' 
                        and sequence='".$d["sequence"]."'"))
            throw new Exception("Аккаунт уже существует");
    }


    private function getMap($isGroup)
    {
        $map = array();

        $map["account"] = array("title" => "Аккаунт", "type" => "info");

        if($isGroup)
            $map["subaccount_count"] = array(
                    "type" => "text",
                    "title" => "Кол-во подключений"
                    );

        $map["host_type"] = array(
                "title" => "Host",
                "type" => "radio",
                "data" => array(array("type" => "array", "array" => array("static" => "static", "dynamic" => "dynamic"))),
                );
        $map["host_static"] = array(
                "title" => "Host IP",
                "type" => "text",
                "condition" => array("eq", "host_type", "static")
                );

        $map["host_port_static"] = array(
                "title" => "Port",
                "type" => "text",
                "condition" => array("eq", "host_type", "static")
                );

        $map["password"] =array(
                "title" => "Пароль",
                "type" => "password_ats2",
                "condition" => array("nq", "host_type", "static")
                );
        $map["dtmf"] = array(
                "title" => "DTMF",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array(
                            "rfc2833" => "rfc2833",
                            "inband" => "inband",
                            "info" => "info"
                            )))
                );

        $map["insecure"] = array(
                "title" => "Авторизация",
                "hint" => "опция: insecure",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array(
                            "" => "Полная",
                            "invite" => "По IP и порту",
                            "invite,port" => "Только по IP"
                            )))
                );
        $map["permit_on"] = array(
                "title" => "Привязка",
                "type" => "select",
                "data" => array(array("type" => "array", "array" => array("yes" => "Да", "no" => "Нет", "auto" => "Авто"))),
                "condition" => array("nq", "host_type", "static")
                );
        $map["permit"] = array(
                "title" => "Привязка к IP",
                "type" => "permit_net",
                "condition" => array("and", array("eq", "permit_on", "yes"),array("nq", "host_type", "static"))
                );
        $map["break"] = array(
                "type" => "break"
                );
        $map["codec"] = array(
                "title" => "Кодеки",
                "type" => "sort_list",
                "data_all" => array(array("type" => "array", "array" => 
                        array("alaw" => "alaw", "g729" => "g729", "gsm" => "GSM", "ulaw" => "ulaw")
                        )),
                );
        $map["context"] = array(
                "title" => "Контекст",
                "type" => "text"
                );

        return $map;
    }

    private function checkForUpdate(&$d)
    {
        $r = account::get($d["id"]);

        $changedTo = array_diff_assoc($d,$r);

        $changedFrom = array();
        foreach($changedTo as $k => $v)
            $changedFrom[$k] = $r[$k];

        if(isset($changedTo["subaccount_count"]))
            $this->change_subaccount($d,$r, $changedFrom["subaccount_count"], $changedTo["subaccount_count"]);


        if($r["is_group"] && $r["subaccount_count"] > 0) // apply group save changes
        {
            $isAllSave = get_param_raw("type_save","all") == "all";

            if($isAllSave)
            {
                $save = $d;
            }else{ // changes save
                $save = $changedTo;
            }

            unset(
                    $save["id"], $save["parent_id"], 
                    $save["is_group"], $save["account"], 
                    $save["subaccount_count"], $save["sequence"]);

            if($save)
            {
                foreach(account::getSubaccounts($r["id"]) as $a)
                {
                    $save["id"] = $a["id"];
                    lineDB::update($save);
                }
            }
        }
        return $changedTo;
    }

    private function change_subaccount(&$d,&$r, $from, $to)
    {
        if($from > $to)
        {
            $d["subaccount_count"] = $from;
            return;
        }

        $sequence = $this->getMaxSequence($d);

        $d1 = $d;

        unset($d1["subaccount_count"], $d1["id"], $d1["c_id"]);
        $d1["is_group"] = 0;
        $d1["parent_id"] = $d["id"];
        $d1["password"] = $r["password"];


        for($i = 0; $i< $to-$from; $i++)
        {
            $d1["sequence"] = $sequence+1+$i;
            $d1["account"] = account::make($d1);
            $d1["password"] = password_gen();

            lineDB::insert($d1);
        }
    }

    private function getMaxSequence(&$d)
    {
        global $db_ats;

        $r = $db_ats->GetValue(
                "select max(sequence) 
                from a_line
                where 
                        serial='".$d["serial"]."' 
                    and is_group = 0");

        if(!$r) return 0;

        return $r;
    }

    private function lookDiffInLines(&$map, &$d)
    {
        global $db_ats;

        $diff = array();

        foreach($db_ats->AllRecords("select * from a_line l, a_connect c where l.c_id = c.id and parent_id = '".$d["id"]."'") as $l)
        {
            foreach(array_keys($d) as $f)
            {
                if(in_array($f, array(
                                "id", "parent_id", "account", 
                                "sequence", "subaccount_count", 
                                "last_update", "enabled", "is_group", "c_id")))
                    continue;

                if($d[$f] != $l[$f])
                {
                    $map[$f]["changed"] = true;
                }
            }
        }
    }

    public function logPassword($s, $event = "view")
    {
            global $user, $db_ats;

            $db_ats->QueryInsert("a_log_password",array(
                        "client_id" => $s["client_id"],
                        "time" => array("NOW()"),
                        "account" => $s["account"],
                        "c_id" => $s["c_id"],
                        "c_type" => "line",
                        "user_id" => $user->Get("id"),
                        "event" => $event
                        )
                    );
    }

    public function ats2_view_pass($fixClient)
    {
        $key = get_param_raw("key", "");

        $id = ats2passcrypt::decryptId($key);

        if($id)
        {
            global $design;

            $design->assign("sip", $s = account::get($id));

            $this->logPassword($s);

            echo $design->fetch("ats2/view_pass.htm");
        }
        exit();
    }

    public function ats2_log_view($fixClient)
    {
        $isFull = get_param_raw("full","") == "true";

        global $db_ats, $design;

        $l = $db_ats->AllRecords("
                    select p.*, u.name,l.id as l_id, m.id as m_id
                    from a_log_password p
                    left join ".SQL_DB.".user_users u on (u.id = p.user_id)                    
                    left join a_line l on (l.id = p.c_id and p.c_type='line')
                    left join a_multitrunk m on (l.id = p.c_id and p.c_type='multitrunk')
                    where l.".sqlClient()." order by p.id desc limit ".($isFull ? 100 : 4));

        if($isFull)
        {
            $isMany = false;
        }else{
            $isMany = count($l) == 4;
            if($isMany)
                unset($l[3]);
        }
        
        $design->assign("log", $l);
        $design->assign("isMany", $isMany);
        $design->AddMain("ats2/log_view.htm");
    }

    public function ats2_number($fixClient)
    {
        include "number.php";
        aNumber::edit();        
    }

    public function ats2_number_del($fixClient)
    {
        include "number.php";
        aNumber::del();
    }

    public function ats2_mt($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "view");
    }

    public function ats2_mt_add($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "add");
    }

    public function ats2_mt_edit($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "edit", get_param_integer("id", 0));
    }

    public function ats2_mt_link($fixclient)
    {
        include "mt_link.php";
        new MTLink($fixclient, get_param_integer("id", 0));
    }

    public function ats2_virtpbx($fixClient)
    {
        include "virtpbx.php";
        aVirtPbx::edit();        
    }

    public function update_key($fixclient)
    {
        global $design, $db_ats;

        $design->assign("update_time", $db_ats->GetValue("select unix_timestamp(date) as date from a_update_client where ".sqlClient()));

        $design->addMain("ats2/update_key.htm");
    }

    public function ats2_set_update($fixclient)
    {
        ats2sync::updateClient(getClientId($fixclient));

        Header("Location: ./?module=ats2");

        exit();
    }

    private function virtpbx_list($fixClient)
    {
        global $design;
        include "virtpbx.php";

        $design->assign("virtpbx", $r = VirtPbx::getList());
        $design->AddMain("ats2/virtpbx_list.htm");
    }

    public function ats2_virtpbx_start($fixClient)
    {
        global $design;

        $clientId = getClientId();
        try{

            $rr = SyncVirtPbx::create($clientId);

            printdbg($rr);

            exit();
            if ($rr = SyncVirtPbx::create($clientId))
            {

                virtPbx::setStarted($clientId);

                header("Location: ./?module=ats2");
                exit();
            }
        } catch(Exception $e)
        {
            $res = $e->getMessage();
            $code = $e->getCode();
            $isErrorResult = $isError = true;

            $result[] = array("number" => "", "action" => "create", "error" => true, "message" => $res, "code" => $code);

            $design->assign("error_full", $result);
            $design->AddMain("ats2/virtpbx_error_save.htm");
            $this->virtpbx_list($fixClient);
        }

    }
}

class account
{
    public function get($id)
    {
        global $db_ats;

        if($id == 0)
        {
            $c = getClient();
            $newAcc = freeAccount::get();
            return array(
                    "id" => "0",
                    "is_group" => "1",
                    "subaccount_count" => 0,
                    "account" => $newAcc["account"],
                    "serial" => $newAcc["serial"],
                    "format" => "ats2",
                    "link_id" => "0",
                    "password" => "********",
                    "parent_id" => "0",
                    "sequence" => "0",
                    "permit" => "",
                    "codec" => "alaw,g729",
                    "context" => "c-realtime-out",
                    "last_update" => "0000-00-00 00:00:00",
                    "enabled" => "yes",
                    "permit_on" => "auto",
                    "host_type" => "dynamic",
                    "host_static" => "",
                    "host_port_static" => "5060",
                    "dtmf" => "rfc2833",
                    "insecure" => ""
                        );
        }

        if(!$id) return false;

        $r = $db_ats->GetRow("select * from a_line l, a_connect c where l.c_id = c.id and l.id = ".$id." and ".sqlClient());
        if($r)
        {
            $r["id"] = $id;

            if($r["is_group"])
                $r["subaccount_count"] = (int)$db_ats->GetValue("select count(1) from a_line where parent_id = '".$r["id"]."'");

            $r["account"] = account::make($r);
        }

        return $r;
    }

    public function make(&$l)
    {
        return 
            sprintf("%06d", $l["serial"]).
            (!isset($l["is_group"]) ||  $l["is_group"] ? "" : sprintf("%02d", $l["sequence"]));
    }

    public function parse($acc)
    {
        $acc = trim($acc);

        if(strlen($acc) == 6 || strlen($acc) == 8)
        {
            if(preg_match("/^([0-9]{6})([0-9]{2})?/", $acc, $o))
            {
                $r = array(
                        "serial" => (int)$o[1],
                        "sequence" => isset($o[2]) ? $o[2] : 0
                        );

                return $r;

            }else{
                throw new Exception("Ошибка добавления аккаунта");
            }
        }else{
            throw new Exception("Ошибка добавления аккаунта");
        }
    }

    public function getSubaccounts($id)
    {
        global $db_ats;

        $dd = array();
        foreach($db_ats->AllRecords("select id from a_line where parent_id = '".$id."'") as $l)
            $dd[] = self::get($l["id"]);

        return $dd;

    }
}


class reservAccount
{
    private function clear()
    {
        global $db_ats;

        $db_ats->Query("delete from a_reserv_account where (date+interval 5 minute) < now()");
    }

    public function get()
    {
        global $db_ats;

        self::clear();

        $sessionId = session_id();

        $v = $db_ats->GetRow("select serial from a_reserv_account where session = '".$sessionId."'");
        if($v)
        {
            $v["account"] = account::make($v);
        }

        return $v;
    }

    public function set($l)
    {
        global $db_ats;

        $db_ats->QueryInsert("a_reserv_account", array(
                    "serial" => $l["serial"],
                    "session" => session_id()
                    )
                );
    }

    public function reset() 
    {
        global $db_ats;

        $db_ats->QueryDelete("a_reserv_account", array(
                    "session" => session_id()
                    )
                );
    }

    public function isReservedOther($serial)
    {
        global $db_ats;

        return $db_ats->GetValue(
                "select serial 
                from a_reserv_account 
                where 
                        serial ='".$serial."' 
                    and session != '".session_id()."'");
    }
}

class freeAccount
{
    public function get()
    {
        $v = reservAccount::get();
        $isReserv = $v ? true : false;

        //printdbg($v, "reserv");

        if(!$isReserv)
        {
            $serial = 100000;
            $inMiss = true;

            $c = 0;
            do
            {
                if($inMiss)
                {
                    // поиск пропущенных
                    $serialMiss = self::getNextMissedAccount($serial);

                    //echo "<br>serialMiss=".$serialMiss." (".$serial.")";
                    if($serialMiss)
                        $serial = $serialMiss;
                }

                if($inMiss && !$serialMiss) 
                    $inMiss = false;

                // поиск максимального
                if(!$inMiss)
                {
                    $serialMax = self::getMaxSerial();
                    //echo "<br>serialMax=".$serialMax." (".$serialMax.")";

                    if($serialMax)
                    {
                        if($serial < $serialMax)
                            $serial = $serialMax+1;
                        else
                            $serial++;
                    }else{
                        $serial = 100001;
                    }

                }
                //echo "<br>".$serial;

                if($c++ > 10) die("c=11");
            }while(reservAccount::isReservedOther($serial));

            $v = array("serial" => $serial);
            $v["account"] = account::make($v);

            reservAccount::set($v);
        }

        return $v;
    }



    private function getNextMissedAccount($serial)
    {
        global $db_ats;

        foreach($db_ats->AllRecords(
                    "select serial 
                    from a_line
                    where 
                            is_group = 1 
                        and serial > ".$serial."
                    order by serial") as $k => $l)
        {
            //printdbg($l, "k".$k);
            if($k+1+$serial != $l["serial"]) return $k+1+$serial;
        }

        return false;
    }

    private function getMaxSerial()
    {
        global $db_ats;

        return $db_ats->GetValue("select max(serial) as v from a_line ");
    }

}


class lineDB
{
    public function insert($d)
    {
        global $db_ats;

        /*
        (
         [account] => 99000001
         [host_type] => dynamic
         [host_static] => 
         [host_port_static] => 5060
         [password] => 679qotqraaju
         [dtmf] => rfc2833
         [insecure] => 
         [permit_on] => no
         [permit] => 
         [codec] => alaw,g729
         [context] => c-realtime-out
         [serial] => 1
         [sequence] => 0
         [is_group] => 1
         [client_id] => 9130
        )
        */

        $line = array();
        foreach(array("parent_id", "account", "serial", "sequence", "is_group", "client_id") as $l)
        {
            $line[$l] = $d[$l];
            unset($d[$l]);
        }


        $lineId = $db_ats->QueryInsert("a_line", $line, true);

        $cId = $db_ats->QueryInsert("a_connect", $d, true);

        $db_ats->QueryUpdate("a_line", "id", array("id" => $lineId, "c_id" => $cId));

        return $lineId;
    }

    public function update(&$d)
    {
        global $db_ats;

        foreach(array("parent_id", "account", "serial", "sequence", "is_group", "client_id", "c_id") as $l)
            unset($d[$l]);

        if(count($d) <= 1) return; // если есть только id, то ничего не делаем

        $d["id"] = self::getConnectId($d["id"]);

        $db_ats->QueryUpdate("a_connect", "id", $d);
    }

    public function getConnectId($lineId)
    {
        global $db_ats;

        return $db_ats->GetValue("select c_id from a_line where id = '".$lineId."' and ".sqlClient());
    }

    public function del($id)
    {
        global $db_ats;

        if($id && $r = account::get($id))
        {
            if($r["is_group"])
            {
                foreach($db_ats->AllRecords("select id, c_id from a_line where parent_id = '".$r["id"]."'") as $l)
                    self::_del($l);
            }

            self::_del($r);
            ats2sync::updateClient($r["client_id"]);
        }
    }

    private function _del($l)
    {
        global $db_ats;

        $db_ats->QueryDelete("a_line", array("id" => $l["id"], "c_id" => $l["c_id"]));
        $db_ats->QueryDelete("a_connect", array("id" => $l["c_id"]));

        $db_ats->QueryDelete("a_link", array("c_id" => $l["id"], "c_type" => "line"));
        $db_ats->QueryDelete("a_link", array("c_id" => $l["id"], "c_type" => "trunk"));

        $db_ats->QueryDelete("a_virtpbx_link", array("type_id" => $l["id"], "type" => "account"));
    }
}

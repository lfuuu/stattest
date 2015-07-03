<?php

define("exception_sql", 1);

include_once PATH_TO_ROOT."modules/ats2/account.php";
include_once PATH_TO_ROOT."modules/ats2/freeaccount.php";
include_once PATH_TO_ROOT."modules/ats2/reservaccount.php";
include_once PATH_TO_ROOT."modules/ats2/linedb.php";

class m_ats2 extends IModule
{

	function GetMain($action,$fixclient){

		if (!isset($this->actions[$action])) return;

		$act=$this->actions[$action];

		if (!access($act[0],$act[1])) return;

        if(!$fixclient) {trigger_error2("Клиент не выбран"); return;}

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
    public function ats2_account_bulk_del($fixclient)
    {
        $del_action = get_param_protected('del_action', 'no');
        if ($del_action != 'no') 
        {
		$accounts = get_param_protected('accounts', array());
		switch ($del_action) 
		{
			case 'account':
				foreach ($accounts as $ids) 
				{
					foreach ($ids as $v)
					{
						lineDB::del($v);
					}
				}
				break;
			case 'link':
				include "number.php";
				aNumber::bulk_del($accounts);
			case 'group':
				$ids = get_param_protected('ids', array());
				if (!empty($ids))
				{
					foreach ($ids as $id)
					{
						lineDB::del($id);
					}
				}
			case 'numbers':
				$numbers = get_param_protected('numbers', array());
				if (!empty($numbers))
				{
					include "number.php";
					foreach ($numbers as $number)
					{
						aNumber::del($number);
					}
				}
			case 'full':
				$numbers = get_param_protected('numbers', array());
				if (!empty($numbers))
				{
					lineDB::bulk_del($numbers);
				}
		}
	}
        header("Location: ./?module=ats2");
        exit();
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
                        account, c_type, n.direction, n.enabled, 
                        m.name, m.parent_id as trunk_id,
                        (
                            select distinct
                                v.id 
                            from 
                                a_virtpbx_link l, a_virtpbx v 
                            where 
                                    v.id = l.virtpbx_id 
                                and v.client_id = '".$_SESSION['clients_client']."'
                                and type='number' 
                                and type_id = n.id
                        ) as is_virtpbx
                    from a_number n 
                    left join a_link k on (k.number_id = n.id) 
                    left join a_line l on (l.id = k.c_id and k.c_type != 'multitrunk') 
                    left join a_multitrunk m on (m.id = k.c_id and k.c_type = 'multitrunk')
                    where n.client_id = {$_SESSION['clients_client']} order by number, k.id") as $n)
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

            if($n["account"])
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
                    where c_id = c.id and client_id = {$_SESSION['clients_client']} 
                    order by serial, is_group desc, sequence") as $l)
        {

            if($l["is_group"])
            {
                $k[$l["id"]] = count($d);
                $l["childs"] = array();
                $d[] = $l;
            }else{
                if(isset($k[$l["parent_id"]]))
                {
                    $d[$k[$l["parent_id"]]]["childs"][] = $l;
                }
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
            trigger_error2("Аккаунт не найден");
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
            $gData["client_id"] = $_SESSION['clients_client'];

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
                "onchange" => "check_if_static()"
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
                "type" => "password_ats2"
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
                "data" => array(array("type" => "array", "array" => array("yes" => "Да", "no" => "Нет", "auto" => "Авто")))
                );
        $map["permit"] = array(
                "title" => "Привязка к IP",
                "type" => "permit_net",
                "condition" => array("eq", "permit_on", "yes")
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

        $changedTo = array_diff_assoc($d, $r);

        $changedFrom = array();
        foreach($changedTo as $k => $v)
            $changedFrom[$k] = $r[$k];

        if(isset($changedTo["subaccount_count"]))
            account::change_subaccount($d, $changedTo["subaccount_count"]);


        if($r["is_group"] && $r["subaccount_count"] > 0) // apply group save changes
        {
            $isAllSave = get_param_raw("type_save", "all") == "all";

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
                    where l.client_id = {$_SESSION['clients_client']} order by p.id desc limit ".($isFull ? 100 : 4));

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

        $design->assign("update_time", $db_ats->GetValue("select unix_timestamp(date) as date from a_update_client where client_id = {$_SESSION['clients_client']}"));

        $design->addMain("ats2/update_key.htm");
    }

    public function ats2_set_update($fixclient)
    {
        ats2sync::updateClient($fixclient);

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

        $clientId = $_SESSION['clients_client'];

        try{
            virtPbx::startVpbx($clientId);

            header("Location: ./?module=ats2");
            exit();
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







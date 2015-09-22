<?php

    function getClientId($client = null)
    {
        global $db;

        if($client == null)
        {
            if(isset($_SESSION["clients_client"]) && $_SESSION["clients_client"])
                $client = $_SESSION["clients_client"];
        }

        if($client === null)
            return 0;

        static $c = array();

        if(!isset($c[$client]))
            $c[$client] = $db->GetValue("select id from ".SQL_DB.".clients where client = '".$db->escape($client)."'");

        return $c[$client];
    }

class m_ats extends IModule
{

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
        $db->Query("use ".SQL_ATS_DB);

        if(!$fixclient && isset($_GET["id"]))
        {
            $d = vSip::get($_GET["id"], false);
            global $fixclient;
            $fixclient = $_SESSION["clients_client"] = $d["client_id"];
        }

        if(substr($action,0,3) == "sip" || in_array($action, array("default", "view_pass", "log_view")))
            include "sip.php";

		call_user_func(array($this,'ats_'.$action),$fixclient);
        $db->Query("use ".SQL_DB);
	}

    public function ats_default($fixClient)
    {
        global $db;

        $this->ats_sip_users($fixClient);
    }

    public function ats_sip_users($fixClient)
    {
        /*
        $region = \app\models\ClientAccount::findOne($fixClient)->region;

        if($fixClient && $region == 99)
        {
            //nothing
        }else
        {
            trigger_error2("Настройки учетных записей SIP для данного региона недоступны");
            return;
        }
        */
        $this->sip_users($fixClient);
        //$this->numbers($fixClient);
    }


	private function sip_users($fixClient, $view = "number", $parentId = 0)
    {
        sip::view($fixClient, $view, $parentId);
        if($view == "number")
            sip::logView();
	}

    public function ats_log_view()
    {
        sip::logView(get_param_raw("full","") == "true");
    }

    public function ats_sip_add($fixClient)
    {
        $this->ats_sip_modify($fixClient);
    }

    public function ats_sip_modify($fixClient)
    {
        sip::modify($fixClient);
    }

    public function ats_sip_action($fixclient)
    {
        if(!access("ats", "support"))
            sip::action($fixclient);
        }


    public function ats_view_pass($fixClient)
    {
        sip::viewPass($fixClient);
    }
    //////////////// external numbsers


    public function ats_test1($fixclient)
    {
        global $db, $design;

        if(($client = get_param_raw("client", "")) != "")
        {
            $_SESSION["clients_client"] = $client;
            header("Location: ./?module=ats&action=sip_users");
            exit();
        }


        $types = array(
                "cpe" => "cpe",
                "line"=> "line",
                "trunk" => "trunk",
                "multitrunk" => "multitrunk",
                "mt" => "mt"
                );
        $type = get_param_raw("type", "cpe");

        $design->assign("data", $db->AllRecords("SELECT s.client, type, concat(n.number,' x ',n.call_count) as callerid FROM  `v_sip` s, v_number n where atype='number' and s.number = n.id
                    and type='".$type."' order by client"));

        $design->assign("type", $type);
        $design->assign("types", $types);
        $design->AddMain("ats/test1.htm");
    }


    public function ats_anonses($fixclient)
    {
        global $design, $db;

        $filePath = "/tmp/anonses/";

        if(($id = get_param_integer("file", 0)) !== 0)
        {
            include MODULES_PATH."ats/anonses.php";
            get_file($filePath);
            exit();
        }elseif(($id = get_param_raw('id','')) !== '')
        {
            // edit
            include MODULES_PATH."ats/anonses.php";
            anonses_edit($filePath);
            $design->AddMain("ats/anonses.htm");
        }else{
            // list

            if(($id = get_param_integer("del", 0)) !== 0)
            {
                //del
                @unlink($filePath.$id.".mp3");
                @unlink($filePath.$id.".alaw");
                $db->Query("DELETE FROM anonses WHERE client_id = $fixclient and id = '".$id."' ");
            }

            $design->assign("anonses_list", $db->AllRecords("SELECT id, name FROM anonses where client_id = $fixclient ORDER BY name"));
            $design->AddMain("ats/anonses_list.htm");
        }
    }

    public function ats_schema($fixclient)
    {
        include "schema.php";
        new Schema($this, $fixclient);
    }

    public function ats_mt($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "view");
    }

    public function ats_mt_add($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "add");
    }

    public function ats_mt_edit($fixclient)
    {
        include "mt.php";
        new MT($fixclient, "edit", get_param_integer("id", 0));
    }

    public function ats_to_lk($fixclient)
    {
        global $db;

        if(!isset($_SESSION["clients_client"]) || !$_SESSION["clients_client"]) trigger_error2("Клиент не установлен!");
        else{

        $client = $db->GetValue("select client from ".SQL_DB.".clients where client='".$_SESSION["clients_client"]."'");
        $session = md5(time()."1111222333".rand(1,100000));

        $db->QueryDelete(SQL_DB.".lk_sess", array("date" => date("Y-m-d H:i:s", strtotime("-1 hour"))));

        $db->QueryInsert(SQL_DB.".lk_sess", array(
                    "session_id" => $session,
                    "client" => $client, 
                    "ip" => $_SERVER["REMOTE_ADDR"]
                    )
                );
        header("Location: http://lk.mcn.ru/fromstat/".$session);
        exit();
        }

    }
}

?>

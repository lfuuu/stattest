<?php


class sip
{

	public static function view($fixClient, $view = "number", $parentId = 0)
    {
		global $db, $design;

		$design->assign('client',  $fixClient);

        if($view == "line")
        {
            $design->assign('sip', 
                    $db->AllRecords($q =
                        "select *,s.id as s_id 
                        from v_sip s
                        where s.".sqlClient()." 
                        and atype ='".$view."'".
                        " and parent_id = '".$parentId."'".
                        " order by s.number, atype"));
        }else{

                $sips = array();

                    foreach($db->AllRecords(
                        $q="select s.*,
                            if(type='multitrunk', s.number, n.number) as number,
                            s.id as s_id, 
                            ns.routing ,
                            s.call_count as s_call_count, 
                            n.call_count as n_call_count,
                            rs.name as `schema`, 
                            rs.id as schema_id,
                            line_mask
                        from v_sip s
                            left join v_number n on (s.number = n.id)
                            left join v_number_settings ns on (ns.sip_id = s.id) 
                            left join r_schema rs on (rs.id = ns.schema_id) 
                        where s.".sqlClient()." 
                        and atype in ('number', 'link') ".
                        " order by enabled, s.number, atype") as $l)
                    {
                        $l["numbers"] = $db->AllRecords($q=
                                "select n.number as n_number,direction,call_count as n_call_count, n.enabled as n_enabled
                                from v_number_mt m, v_number n 
                                where sip_id = ".$l["s_id"]." and n.id = m.number_id 
                                order by n.enabled desc, number");

                        if($l["type"] == "multitrunk")
                            $l["m_call_count"] = $db->GetValue("select call_count from v_sip where id = '".$l["parent_id"]."'");

                        $sips[] = $l;
                    }
                    $s = array();
                    foreach($sips as $l)
                    {
                        $count = count($l["numbers"]);

                        $m = $l;
                        if(isset($l["numbers"][0]))
                            $m = array_merge($l, $l["numbers"][0]);
                        $m +=array("count" => $count);
                        $s[] = $m;

                        foreach($l["numbers"] as $idx => $n)
                        {
                            if($idx == 0) continue;
                            $s[] = $n+array("is_number" => true);
                        }
                    }

            $design->assign('s1', $s);
            $design->assign('s1free', vNumber::getFree());
            $design->assign('sip', $sips);
        }

        $design->assign("isAllowAdd", self::isAllowAdd());
        $design->assign("view", $view);

		$design->AddMain('ats/sip.htm');
	}


    public static function modify($fixClient)
    {
        global $db, $design;
        include_once INCLUDE_PATH."formconstructor.php";

        $id = get_param_raw("id", 0);

        if(get_param_raw("cancel", "") !== "") // cancel button
        {
            if($id == 0)
            {
                header("Location: ./?module=ats&action=sip_users");
            }else{
                $info = self::getInfo($id, $fixClient);
                if($info["atype"] == "line")
                {
                    header("Location: ./?module=ats&action=sip_modify&id=".$info["number"]["id"]);
                }else{
                    header("Location: ./?module=ats&action=sip_users");
                }
            }
            exit();
        }

        if($id == 0)
        {
            if(!self::isAllowAdd() || access("ats", "support"))
            {
                header("Location: ./?module=ats&action=sip_users");
                exit();
            }
        }

        $data = vSip::get($id);

        if(!$data && $id)
        {
            $data = vSip::get($id, false);
            if($data &&  $fixClient != self::getClientById(self::resolveClientId($data["client_id"])))
            {
                $_SESSION["clients_client"] = $fixClient = self::getClientById($data["client_id"]);
            }
        }


        $map = self::getMap($data["atype"], $data["type"], $id);

        if(access("ats", "support"))
            $map = self::map_supportMod($map, $data);

        if($data["type"] == "line" && $id > 0)
        {
            self::lookDiffInLines($map, $id, $data);
        }

        $formConstr = new FormConctructor($map);

        $error = "";
        $isSaved = get_param_raw("saved", "") == "ok";

        if(get_param_raw("do", ""))
        {
            $gData = $formConstr->gatherFromData($id);
            self::preparaGet($gData);

            $gData["id"] = $id;
            $gData["atype"] = $data["atype"];


            if($id != 0)
                $gData["number"] = $data["number"];

            try
            {
                self::check($gData, $data, $map);
            }catch(Exception $e) {
                $error = $e->GetMessage();;
            }


            if($error)
            {
                $formConstr->make($gData);
            }else{

                $gData["client_id"] = getClientId();

                if(isset($gData["password"]))
                {
                    if($gData["password"] == "********") // не изменяем пароль, если он не поменялся
                    {
                        unset($gData["password"]);
                    }elseif($data["id"] != 0){
                        vSip::logPasswordView($gData, "change");
                    }
                }

                if(($isSaved = (self::apply($gData, $data) && $data["id"] != 0)) || $data["atype"] == "line")
                {
                    $info = self::getInfo($data["id"], $fixClient);
                    header("Location: ./?module=ats&action=sip_modify&id=".$info["number"]["id"].($isSaved ? "&saved=ok":""));
                }else{
                    header("Location: ./?module=ats&action=sip_users");
                }
                //echo "ok :)";
                exit();
            }
        }else{

            if($data["password"] && $id != 0) // скрываем пароль, если не добавление
                $data["password"] = "********";

            $data["key"] = self::cryptId($data["id"]);
            $formConstr->make($data);
        }


        $design->assign("is_form_open", !($data["atype"] == "number" && ($data["type"] == "cpe" || $data["type"] == "line")) || $error || $data["id"] == 0);
        $design->assign("show_hide_key", $data["atype"] == "number" && ($data["type"] == "cpe" || $data["type"] == "line") && $data["id"] > 0);
        $design->assign("error", $error);
        $design->assign("isSaved", $isSaved);

        $design->AddMain("ats/sip_add.htm");

        if($data["id"] > 0 && ($data["type"] == "cpe" || $data["type"] == "line"))
        {
            self::view($fixClient, "line", $data["atype"] == "number" ? $data["id"] : $data["parent_id"]);
        }
    }

    private static function apply(&$d, &$s) //(to save, in db)
    {
        global $db;

        // если создаем привязку к мультитранку, а привязка уже есть.
        if($d["id"] == 0 && $d["type"] == "link")
        {
            $sipId = $db->GetValue($q=
                    "select id from v_sip 
                    where type='".$d["link_type"]."' and atype='link' 
                    and client_id='".$d["client_id"]."' and parent_id ='".$d["parent_id_".$d["link_type"]]."'");

            if($sipId)
            {
                $d["id"] = $sipId;
                $p = numberMT::load($sipId);
                if($p)
                    $d["numbers_mt"] .= ($d["numbers_mt"] ? ",": "").$p;

                $s = vSip::get($sipId);
            }
        }



        if($d["id"] == 0) // add
        {
            if(!self::isAllowAdd())
            {
                header("Location: ./?module=ats&action=sip_users");
                exit();
            }

            if($d["atype"] == "link")
            {
                $d["number"] = $db->GetValue("select number from v_sip where id ='".$d["parent_id_".$d["link_type"]]."'");
            }

            vSip::add($d);

            return true;
        }else{ // edit

            if($s["type"] == "link")
            {
                unset($d["parent_id"]);
            }

            $r = vSip::apply($d, $s, get_param_raw("type_save", "") == "all");

            return $r;
        }
    }

    private static function lookDiffInLines(&$map, $id, &$data)
    {
        global $db;

        $r = $db->GetRow("select * from v_sip s, v_number_mt n where s.id = '".$id."' and s.id = n.sip_id");
        $d = array();

        foreach($db->AllRecords("select * from v_sip s, v_number_mt n where parent_id = '".$id."' and s.id=n.sip_id") as $l)
        {
            foreach(array_keys($r) as $f)
            {
                if(in_array($f, 
                            array("id", "atype", "type", 
                                "number", "parent_id", "num", 
                                "century", "lines","call_count", 
                                "host_port_static", "line_pref", 
                                "enabled")
                            )
                        ) continue;

                if($r[$f] != $l[$f])
                {
                    $d[$f] = 1;
                }
            }
        }

        foreach($d as $f => $devNull)
        {
            if(isset($map[$f]))
                $map[$f]["changed"] = true;
        }
    }

    private static function preparaGet(&$d)
    {
        foreach(array("enabled", /*"t38", "nat", */"is_pool"/*, "permit_on"*/) as $v)
        {
            if(isset($d[$v]) && $d[$v] == "") $d[$v] = "no";
        }

        if(isset($d["host_type"]) && $d["host_type"] == "static") $d["password"] = "";
    }

    public static function action($fixclient)
    {
        global $db;

        $action = get_param_raw("sip_action","");

        $ids = get_param_raw("ids", array());
        $superId = get_param_raw("super_id", 0);


        foreach($ids as $id)
        {
            $info = self::getInfo($id, $fixclient);

            if ($action == "delete_with_sync")
            {
                if (isset($info["number"]["number"]) && $info["number"]["number"])
                {
                    self::_markDisabledinDB($info["number"]["number"]);
                }
            }

            if($action == "delete" || $action == "delete_with_sync")
            {
                vSip::del($id, $info, $fixclient);
            }elseif($action == "enable" || $action == "disable")
            {
                vSip::setEnabled($info, $action == "enable");
            }
        }

        if($superId)
        {
            if($db->GetValue("select `lines` from v_sip where id = '".$superId."' and ".sqlClient()) == 0)
            {
                $db->Query("delete FROM `v_sip` where id ='".$superId."' and ".sqlClient());
                $db->QueryDelete("v_number_settings", array("sip_id" => $superId));
                $superId = 0;
            }
        }

        if($superId == 0)
        {
            header("Location: ./?module=ats&action=sip_users");
        }else{
            header("Location: ./?module=ats&action=sip_modify&id=".$superId);
        }
        exit();
    }

    private static function _markDisabledinDB($number)
    {
        static $conn = null;

        if ($conn === null)
            $conn = @pg_connect("host=".R_CALLS_99_HOST." dbname=".R_CALLS_99_DB." user=".R_CALLS_99_USER." password=".R_CALLS_99_PASS." connect_timeout=1");

        if (!$conn) 
            throw new Exception("Connection error (PG HOST: ".R_CALLS_99_HOST.")");

        $res = @pg_query("SELECT * FROM extensions WHERE exten = '".$number."' AND enabled = 't'");

        if (!$res) 
            throw new Exception("Query error (PG HOST: ".R_CALLS_99_HOST.")");

        if ($l = pg_fetch_assoc($res))
        {
            pg_query("UPDATE extensions SET enabled='f' WHERE exten = '".$number."' and priority = 1");
        }
    }

    private static function check(&$data, &$od, &$map)
    {
        include INCLUDE_PATH."checker.php";

        if($od["id"] > 0 && $od["atype"] == "link" || (isset($data["type"]) && $data["type"] == "link")) 
        {
            checker::isEmpty($data["numbers_mt"], "Не заданны номера!");
            return;
        }


        if(isset($map["host_type"]))
        {
            if($od["id"] == 0 || $od["atype"] != "link")
            {
                if($data["host_type"] != "static")
                    checker::isEmpty($data["password"], "Пароль не задан!");
            }
        }

        if($od["atype"] == "number" && $od["id"] == 0)
        {
            checker::isEmpty($data["number"], "Номер не задан!");
            checker::isDigits($data["number"], "Номер задан не верно!");
            checker::isUsed($data["number"], "number", "v_sip", $data["id"], "Данный номер уже используется");
        }

        if($od["atype"] == "number" && $od["type"] == "cpe")
        {
            checker::isDigits($data["lines"], "Количество линий задано не верно!");
            checker::number_isBetween($data["lines"], 1,100, "Количество линий задано не верно!");
        }

        if($od["atype"] == "number" && $od["id"] == 0)
        {
            if($data["line_mask"])
            {
                //checker::isEmpty($data["line_mask"], "Маска линии должна быть обязательно");
                checker::isAlnum($data["line_mask"], "Проверте правильность введенной маски");
            }
        }

        if(isset($map["host_type"]))
            if($data["host_type"] == "static")
            {
                checker::isEmpty($data["host_static"], "Не задан статический host!");
                checker::isValideIp($data["host_static"], "Статический host задан не верно!");

                if($data["atype"] == "line")
                {
                    checker::isDigits($data["host_port_static"], "Статический порт задан не верно!");
                    checker::number_isBetween($data["host_port_static"], 1, 65536, "Статический порт задан не верно!");
                }

                if($od["id"] == 0 && $data["permit_on"] == "yes")
                {
                    if(strpos($data["permit"], $data["host_static"]) === false)
                    {
                        $data["permit"] .= ($data["permit"] ? "," : "").$data["host_static"]."/32";
                    }
                }else{
                    //checker::isEmpty($data["permit"], "Если задан статический Host IP, необходимо сделать привязку");
                }
            }

        if(isset($map["permit_on"]))
            if($data["permit_on"] == "yes" && $data["host_type"] != "static")
                checker::isEmpty($data["permit"], "Привязка включена, но не задана!");

        if(isset($map["codec"]))
            checker::isEmpty($data["codec"], "Задайте кодеки!");

        if(isset($map["context"]))
            checker::isEmpty($data["context"], "Контекст не задан!");
    }

    private static function getMap($atype, $type, $id)
    {
        global $db, $user, $fixclient;

        $ff = $db->AllRecords("explain v_sip");
        $vs = array();
        foreach($ff as $f)
        {
            if(preg_match_all("/enum\(([^)]+)\)/", $f["Type"], $o))
            {
                $vs[$f["Field"]] =array();
                foreach(explode(",", str_replace("'","",$o[1][0])) as $v)
                {
                    $vs[$f["Field"]][$v] = $v;
                }
                $vs[$f["Field"]] = array("type" => "array", "array" => $vs[$f["Field"]]);
            }

            if($f["Default"] != "")
            {
                $vd[$f["Field"]] = $f["Default"];
            }
        }

        $vs["direction"] = array("type" => "array", "array" => array(
                        "full" => "Full",
                        "russia" => "Russia",
                        "mskmob" => "MskMob",
                        "msk" => "Msk"
                        )
                );
        global $design, $db;;


        $trunks = array();
        foreach($db->AllRecords("select s.id, n.number from v_sip s, v_number n 
                                where s.".sqlClient()." and s.number=n.id 
                                and s.type='trunk' and s.atype='number' 
                                order by number") as $l)
        {
            $trunks[$l["id"]] = $l["number"];
        }

        $multitrunks = array();
        foreach($db->AllRecords("select id, number from v_sip where type='multitrunk' and atype='multitrunk' order by number") as $l)
            $multitrunks[$l["id"]] = $l["number"];

        $design->assign("direction", $vs["direction"]["array"]);

        /* conditions */
        $dYesNo  = array("type" => "array", "array" => array("yes" => "Да","no" => "Нет"));
        $dcYesNo = array("type" => "array", "array" => array("yes" => ""));

        $types = array(
                //"cpe" => "CPE",
                "line" => "Line",
                "trunk" => "Trunk",
                );

        if($multitrunks || $trunks)
                $types["link"] = "Привязка";

        $codecs = array("alaw" => "alaw", "g729" => "g729", "gsm" => "GSM", "ulaw" => "ulaw");

        $m = array();

        if($atype == "number")
        {
            if($id == 0)
            {
                $m["type"] = array(
                        "title" => "Тип устройства",
                        "type" => "radio",
                        "data" => array(array("type" => "array", "array" => $types))
                        );
            }elseif($type == "link"){
                $m["type"] = array(
                        "type" => "hidden",
                        );
            }else{
                $m["type"] = array(
                        "title" => "Тип устройства",
                        "type" => "info",
                        );
            }
        }

        if($atype == "number" && $id == 0)
        {
            $m["number"] = array(
                    "title" => "Номер",
                    "mask" => "number",
                    "type" => "number_lines_select",
                    "condition" => array("nq", "type" , "link"),
                    "data" => array(array("type" => "sql", "sql" => "select id, number, `call_count`  from v_number where ".sqlClient()."
                            and id in ('".implode("','", array_keys(vNumber::getFree()))."')
                            "))
                    );

            /*
               $m["number_mt"] = array(
               "title" => "Название транка",
               "type" => "info",
               "condition" => array("eq", "type" , "multitrunk")
               );
             */
        }

        if($id == 0 || $atype == "link")
        {
            if($id == 0)
            {
                if($multitrunks || $trunks)
                {
                    $m["link_type"] = array(
                            "title" => "Привязать к",
                            "type" => "select",
                            "data" => array(
                                array("type" => "array", "array" => array())
                                )
                            );

                    if($multitrunks)
                        $m["link_type"]["data"][0]["array"]["multitrunk"] = "MultiTrunk";

                    if($trunks)
                        $m["link_type"]["data"][0]["array"]["trunk"] = "Trunk";


                    $m["link_type"]["condition"] = array("eq", "type", "link");
                }
            }else{
                $m["link_type"] = array(
                        "title" => "Привязан к",
                        "type" => "info"
                        );
            }

            if($id == 0)
            {
                if($multitrunks)
                {
                    $m["parent_id_multitrunk"] = array(
                            "title" => "Мультитранк",
                            "type" => "select",
                            "data" => array(
                                array("type" => "array", "array" => $multitrunks)
                                )
                            );
                    $m["parent_id_multitrunk"]["condition"] = array("and", array("eq", "type", "link"),  array("eq", "link_type", "multitrunk"));
                }
            }elseif($atype == "multitrunk"){
                $m["parent_id_multitrunk"] = array(
                        "title" => "Мультитранк",
                        "type" => "info"
                        );
            }

            if($id == 0)
            {
                if($trunks)
                {
                    $m["parent_id_trunk"] = array(
                            "title" => "Транк",
                            "type" => "select",
                            "data" => array(
                                array(
                                    "type"  => "array", 
                                    "array" => $trunks
                                    )
                                )
                            );
                    $m["parent_id_trunk"]["condition"] = array("and", array("eq", "type", "link"),  array("eq", "link_type", "trunk"));
                }
            }elseif($atype == "trunk"){
                $m["parent_id_trunk"] = array(
                        "title" => "Транк",
                        "type" => "info"
                        );
            }
        }

        if($atype == "number")
        {
            if($id != 0 && $type != "link")
            {
                $m["call_count"] = array(
                        "title" => "Одноврем. разговоров",
                        "hint" => "Количество одновременно идущих разговоров",
                        "type" => "info",
                        );
            }

            if($type == "cpe" || $type == "line")
            {
                $m["lines"] = array(
                        "title" => "Кол-во линий",
                        "type" => "text",
                        );
                if($id == 0)
                    $m["lines"]["condition"] = array("or" ,array("eq", "type", "cpe"), array("eq", "type", "line"));
            }

            if($type == "line" || $id == 0)
            {
                $m["line_mask"] = array(
                        "title" => "Префикс логина",
                        "type" => $id == 0 ? "text" : "info"
                        );
                if($id == 0)
                    $m["line_mask"]["condition"] = array("eq", "type", "line");
            }

            if($type == "cpe" || $type == "line")
            {
                $m["delimeter"] = array(
                        "title" => "Разделитель",
                        "type" => "select",
                        "data" => array(array("type" => "array", "array" => array("+" => "+", "*" => "*","" => "Нет"))),
                        "default" => "+"
                        );
                if($id == 0)
                    $m["delimeter"]["condition"] = array("or" ,array("eq", "type", "cpe"), array("eq", "type", "line"));
            }
        }

        if($id == 0 || $atype != "link")
        {
            $m["host_type"] = array(
                    "title" => "Host",
                    "type" => "radio",
                    "data" => array($vs["host_type"]),
                    );

            if($id == 0)
                $m["host_type"]["condition"] = array("nq", "type" , "link");

            $m["host_static"] = array(
                    "title" => "Host IP",
                    "type" => "text",
                    "condition" => array("eq", "host_type", "static")
                    );

            if($id == 0)
                $m["host_static"]["condition"] = array("and", $m["host_static"]["condition"], array("nq", "type" , "link"));

            if($type == "line")
            {
                $m["host_port_static"] = array(
                        "title" => "Port",
                        "type" => "text",
                        "condition" => array("eq", "host_type", "static")
                        );

                if($id == 0)
                    $m["host_port_static"]["condition"] = array("and", $m["host_port_static"]["condition"], array("nq", "type" , "link"));
            }
        }

        if($id == 0 || $atype != "link")
        {
            $m["password"] =array(
                    "title" => "Пароль",
                    "type" => "password_ats",
                    "condition" => array("nq", "host_type", "static")
                    );
            if($id == 0)
                $m["password"]["condition"] = array("and" , $m["password"]["condition"], array("nq", "type" , "link"));
        }



        /*
           $m["nat"] = array(
           "title" => "NAT",
           "type" => "checkbox",
           "data" => array($dcYesNo)
           );
         */

        if($id == 0 || $atype != "link")
        {
            $m["dtmf"] = array(
                    "title" => "DTMF",
                    "type" => "select",
                    "data" => array($vs["dtmf"])
                    );

            if($id == 0)
                $m["dtmf"]["condition"] = array("nq", "type" , "link");
        }

        /*
           $m["connect_type"] = array(
           "title" => "Тип",
           "type" => "radio",
           "data" => array(array("type" => "array", "array" => array("friend" => "Friend", "peer" => "Peer")))
           );
         */

        if($id == 0 || $atype != "link")
        {
           $m["insecure"] = array(
           "title" => "Авторизация",
           "hint" => "опция: insecure",
           "type" => "select",
           "data" => array(array("type" => "array", "array" => array(
                       "" => "Полная",
                       "invite,port" => "По IP и порту",
                       "port" => "Только по IP"
                       )))
           );
        }

        if($id == 0 || $atype != "link")
        {
            $m["permit_on"] = array(
                    "title" => "Привязка",
                    "type" => "select",
                    "data" => array(array("type" => "array", "array" => array("yes" => "Да", "no" => "Нет", "auto" => "Авто"))),
                    "condition" => array("nq", "host_type", "static")
                    );

            if($id == 0)
                $m["permit_on"]["condition"] = array("and", $m["permit_on"]["condition"], array("nq", "type", "link"));


            $m["permit"] = array(
                    "title" => "Привязка к IP",
                    "type" => "permit_net",
                    "condition" => array("and", array("eq", "permit_on", "yes"),array("nq", "host_type", "static"))
                    );
        }

        $m["break"] = array(
                "type" => "break"
                );

        if($id == 0 || ($id != 0 && $atype != "link"))
        {
            $m["codec"] = array(
                    "title" => "Кодеки",
                    "type" => "sort_list",
                    "data_all" => array(array("type" => "array", "array" => $codecs)),
                    );

            if($id == 0)
                $m["codec"]["condition"] = array("nq", "type", "link");
        }

        /*
           $m["t38"] = array(
           "title" => "Fax T38",
           "type" => "checkbox",
           "data" => array($dcYesNo)
           );
         */

        // в добавлении, не в редактировании самой линии, не в link'е
        if($id == 0 || $atype != "link" )
        {
            $m["direction"] = array(
                    "title" => "Разрешенные направления",
                    "type" => "select",
                    "data" => array($vs["direction"])
                    );

            if($id == 0)
                $m["direction"]["condition"] = array("nq", "type", "link");
        }


        if($id == 0 || $atype != "link")
        {
            $m["context"] = array(
                    "title" => "Контекст",
                    "type" => "text"
                    );
            if($id == 0)
                $m["context"]["condition"] = array("nq", "type", "link");
        }

        if($id == 0 || $atype == "link")
        {
            $m["numbers_mt"] = array(
                    "title" => "Номера",
                    "type" => "multitrunk_numbers",
                    "data_all" => array(array("type" => "query", "query" => "
                            select id, concat(number,'x', `call_count`) as l from `v_number` 
                            where ".sqlClient()." and id in ('".implode("','", array_keys(vNumber::getFree($id)))."') order by number"))
                    );

            if($id == 0){
                $m["numbers_mt"]["condition"] = array("eq", "type", "link");
            }
        }
        return $m;
    }

    private static function isAllowAdd()
    {
        return count(vNumber::getFree()) > 0;
    }

    public static function getInfo($id, $fixclient)
    {
        global $db;
        $data = $db->GetRow("select * from v_sip where id ='".$id."' and ".sqlClient());
        $d = array("id" => $id, "atype" => $data["atype"], "all" => array());

        if($data["atype"] == "line")
        {
            $data = $db->GetRow("select * from v_sip where ".sqlClient()." and id='".$data["parent_id"]."' and atype='number'");
        }

        if($data["atype"] == "number")
        {
            $d["number"] = array("id" => $data["id"], "sip_id" => $data["number"], "number" => $db->GetValue("select number from v_number where id = '".$data["number"]."'") ,"maxline" => 0);
            $d["all"][] = $data["id"];
            $d["lines"] = array();

            foreach($db->AllRecords("select id, number from v_sip where ".sqlClient()."
                        and parent_id='".$data["id"]."' and atype = 'line'") as $l)
            {
                $d["lines"][] = $l["id"];
                $d["all"][] = $l["id"];

                if(preg_match_all("/^\d+[*+](\d{1,})$/", $l["number"], $o))
                    if($d["number"]["maxline"] < $o[1][0]) 
                        $d["number"]["maxline"] = $o[1][0];
            }
        }
        return $d;
    }

    public function viewPass()
    {
        $key = get_param_raw("key", "");

        $id = self::decryptId($key);

        if($id)
        {
            global $design;

            $design->assign("sip", $s = vSip::get($id));

            vSip::logPasswordView($s);

            echo $design->fetch("ats/view_pass.htm");
        }
        exit();
    }

    public static function logView($isFull = false)
    {
        global $db, $design;

        $l = $db->AllRecords("
                    select l.*, u.name,s.id as s_id
                    from log_password l
                    left join ".SQL_DB.".user_users u on (u.id = l.user_id)                    
                    left join v_sip s on (s.id = l.sip_id)
                    where l.".sqlClient()." order by id desc limit ".($isFull ? 100 : 4));
        
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
        $design->AddMain("ats/log_view.htm");
    }

    private function map_supportMod(&$map, &$data)
    {
        $allowFields = array("type", "call_count", "break", "direction","password");

        if($data["host_type"] == "dynamic" && $data["permit_on"] == "yes")
            $allowFields[] = "permit";

        $newMap = array();
        foreach($map as $k => $v)
            if(in_array($k, $allowFields))
                $newMap[$k] = $v;

        if(isset($newMap["permit"]))
            unset($newMap["permit"]["condition"]);

        return $newMap;
    }

    private static function cryptId($id, $salt = null)
    {
        $t = $salt === null ? time() : $salt;
        return $id.":".$t.":".md5($id.$t."aaa".$t);
    }

    private static function decryptId($str)
    {
        list($id, $salt, $md5) = explode(":", $str."::::");
        return self::cryptId($id, $salt) == $str ? $id : false;
    }

    private static function resolveClientId($fixclient)
    {
        global $db;
        return $db->GetValue("select id from ".SQL_DB.".clients where id = '".$fixclient."' or client = '".$fixclient."'");
    }

    private static function getClientById($id)
    {
        global $db;
        return $db->GetValue("select client from ".SQL_DB.".clients where id = '".$id."'");
    }
}

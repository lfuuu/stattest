<?php

class vSip
{

    public static function get($id, $withFixClient = true)
    {
        $sqlClient = $withFixClient ? " and s.".sqlClient() : "";

        if($id == 0)
        {
            global $fixclient;

            $data = array(
                    "id" => 0,
                    "atype" => "number",
                    "lines" => 2,
                    "type" => "line",
                    "password" => self::passGen(),
                    "host_type" => "dynamic",
                    "host_port_static" => 5060,
                    "codec" => "alaw,g729",
                    "direction" => "full",
                    "permit_on" => "auto",
                    "permit" => "",
                    "numbers_mt" => "",
                    "is_pool" => "no",
                    "line_mask" => self::normalizeClient($fixclient),
                    "context" => "c-realtime-out",
                    "insecure" => ""
                    //"t38" => "yes",
                    //"connect_type" => "friend",
                    //"nat" => "yes"
                    );
        }else{
            global $db;

            $data1 = $db->GetRow($q="
                        select 
                            s.*,if(type='multitrunk', s.number, n.number) as number,n.call_count,s.id as s_id, n.id as number_id
                        from v_sip s
                        left join v_number n on (s.number = n.id)
                        where 1 ".$sqlClient." 
                            and s.id = '".$id."'
                            and atype in ('number','link')
                            ");

            if($data1)
                if($data1["atype"] == "link")
                {
                    $data1["link_type"] = $data1["type"];
                    $data1["type"] = "link";

                    $data1["parent_id_multitrunk"] = 0;
                    $data1["parent_id_trunk"] = 0;

                    $data1["parent_id_".$data1["link_type"]] = $data1["parent_id"];
                }

            //load numbers
            if($data1)
            {
                if($data1["atype"] == "link")
                {
                    $data1["numbers_mt"] = numberMT::load($id);
                }else{
                    $mt = numberMT::load($id, true);
                    $data1["direction"] = $mt[$data1["number_id"]];
                }
            }

            $data2 = $db->GetRow($q="select s.*, n.number_id, n.direction, s.id as s_id from v_sip s, v_number_mt n where s.id ='".$id."' ".$sqlClient." and atype='line' and s.id=n.sip_id");

            $data = $data1 ? $data1 : $data2;
            if($data["atype"] == "link"){
                if($data["link_type"] == "trunk")
                {
                    $data["parent_id_".$data["link_type"]] = $db->GetValue(
                            "select n.number from v_sip s, v_number n where s.id = '".$data["parent_id"]."' and s.number = n.id");
                }else{ //multitrunk
                    $data["parent_id_".$data["link_type"]] = $db->GetValue("select number from v_sip where id = '".$data["parent_id"]."'");
                }
            }
        }
        return $data;
    }

    public static function add(&$d)
    {
        global $db;

        $d["call_count"] = numberMT::getCallCount($d["atype"] == "link" ?  $d["numbers_mt"] : $d["number"]);

        $numbersMT = $d["numbers_mt"];
        unset($d["numbers_mt"]);

        if($d["type"] == "line" && !$d["line_mask"]) // line mask by default
        {
            $d["line_mask"] = $db->GetValue("select number from v_number where id = '".$d["number"]."'");
        }

        if($d["type"] == "link")
        {
            $d["type"] = $d["link_type"];
            $d["atype"] = "link";
            $d["parent_id"] = $d["parent_id_".$d["type"]];
            $d["number"] = $db->GetValue("select number from v_sip where id = '".$d["parent_id"]."'");
        }else{
            $numbersMT = $d["number"]."=".$d["direction"];
        }

        unset($d["direction"]);

        unset($d["id"],$d["link_type"], $d["parent_id_trunk"], $d["parent_id_multitrunk"]);

        $parentId = $db->QueryInsert("v_sip", $d);
        //$db->QueryInsert("v_number_settings", array("sip_id" => $parentId, "client_id" => $d['client_id']));

        numberMT::save($parentId, $numbersMT);

        vNotify::anonse("sip:add:".$parentId);

        if($d["type"] == "cpe" || $d["type"] == "line")
        {
            $maxLinePref= 0;//$d["line_mask"] ? self::getMaxLinePrefInClient($d["line_mask"]) : self::getMaxLinePref($parentId);

            for($i=0; $i<$d["lines"]; $i++)
            {
                $d1 = $d;
                $d1["atype"] = "line";

                if($d1["type"] == "line")
                {
                    // установленна - берем ее, нет - начинт номер и "+"
                    $d1["number"] = $d["line_mask"].$d["delimeter"].(++$maxLinePref);
                    $d1["line_pref"] = $maxLinePref;
                }

                //$d1["number"] =$number["number"].$d["delimeter"].($i+1);
                $d1["parent_id"] = $parentId;
                numberMT::save($db->QueryInsert("v_sip", $d1), $numbersMT);
            }
        }

        if($d["atype"] == "link")
            self::recalcCallCount($parentId);

    }

    public static function del($id)
    {
        global $db;

        $d = $db->GetRow("select atype,type, parent_id from v_sip where id ='".$id."'");

        if($d["atype"] == "number")
        {
            self::_delNumber($id);
        }else{
            self::_delLine($id);
        }


        if($d["atype"] == "link")
        {
            self::recalcCallCount($id, $d["parent_id"]);
        }
    }

    private static function _delNumber($id)
    {
        global $db;

        $n = $db->GetRow("select type, number from v_sip where id=".$id);
        if(!$n) return;
        $db->Begin();

        $mtNumbers = array();
        foreach($db->AllRecords("select number_id from v_number_mt where sip_id = '".$id."'") as $l)
            $mtNumbers[] = $l["number_id"];

        if($mtNumbers)
            $db->Query("delete from v_number where id in ('".implode("','", $mtNumbers)."') and enabled='no'");

        $db->QueryDelete("v_number_mt", array("sip_id" => $id));
        $db->QueryDelete("v_sip",array("id" => $id));
        $db->QueryDelete("v_sip",array("parent_id" => $id));
        $db->QueryDelete("v_number_settings", array("sip_id" => $id));
        $db->Commit();
        vNotify::anonse("sip:del:".$id);
    }
    private static function _delLine($id)
    {
        global $db;

        $sId = $db->GetValue("select parent_id from v_sip where id = '".$id."'");

        $db->QueryDelete("v_sip", array("id" => $id));
        $db->QueryDelete("v_number_mt", array("sip_id" => $id));
        $db->Query("update v_sip set `lines` = `lines`-1 where '".$sId."' in (id,parent_id)");

        vNotify::anonse("sip:del_line:".$id);
    }

    public static function setEnabled($info, $isEnable)
    {
        global $db;

        $fromStatus = !$isEnable ? "no" : "yes";
        $toStatus = $isEnable ? "no" : "yes";

        $db->Query($q = "update v_sip 
                set is_stoped ='".$toStatus."' 
                where id ".($info["atype"] == "number" ? " in ('".implode("','",  $info["all"])."')" : " = '".$info["id"]."'")." 
                and is_stoped = '".$fromStatus."'");
    }






































    public static function apply($d, $s, $isAllSave)
    {
        global $db;

        /*
        include "apply.php";

        a::apply($d, $s,$isAllSave);
        */

        $lineNumberMt = "";
        if($s["atype"] == "number" && $s["type"] == "line")
        {
            $lineNumberMt = $s["number_id"]."=".$s["direction"];
        }


        if($d["atype"] == "link")
        {
            $d["type"] = $d["link_type"];
        }

        $insertNum = $updateNum = $update = $insert = array();
        foreach($s as $f => $v)
        {
            if(isset($d[$f]) && $d[$f] != $v) 
                $update[$f] = $d[$f];

            $insert[$f] = isset($d[$f]) ? $d[$f] : $v;
        }

        // this field only in line|trunk
        if(isset($update["direction"]))
        {
            $updateNum = array(
                    "direction" => $update["direction"],
                    "sip_id"    => $s["s_id"],
                    "number_id" => $s["number_id"]
                    );

            unset($update["direction"]);
        }
        unset($insert["direction"], $insert["number_id"]);



        if($isAllSave)
        {
            $update = $insert;
            unset($update["atype"], $update["parent_id"], $update["s_id"], $update["line_pref"]);
        }


        if($update)
        {
            $update["id"] = $s["id"];
            $update["client_id"] = $s["client_id"];
            $numbersMT = isset($update["numbers_mt"]) ? $update["numbers_mt"] : "";
            unset($update["numbers_mt"], $insert["numbers_mt"], $insert["number_id"]);


            if($s["atype"] == "number")
            {
                $info = sip::getInfo($d["id"], $d["client_id"]);
                if(isset($update["delimeter"]))
                {
                    $update["number"] = array("concat(line_mask, '".$update["delimeter"]."', line_pref)");
                }

                if(isset($update["lines"]))
                {
                    if(count($info["lines"]) < $update["lines"])
                    {
                        $s2 = $insert;

                        if($s2["type"] == "line")
                        {
                            $maxLinePref = $s2["line_mask"] ? 
                                self::getMaxLinePrefInClient($s2["line_mask"]) : 
                                self::getMaxLinePref($info["number"]["id"]);

                        }

                        $s2["atype"] = "line";
                        $s2["parent_id"] = $info["number"]["id"];
                        unset($s2["id"], $s2["s_id"]);

                        for($i = 0; $i < $update["lines"] - count($info["lines"]); $i++)
                        {
                            if($s2["type"] == "line")
                            {
                                $s2["number"] = ($s2["line_mask"] ? $s2["line_mask"] : $info["number"]["number"]).$s2["delimeter"].(++$maxLinePref);
                                $s2["line_pref"] = $maxLinePref;
                            }else{//cpe
                                $s2["number"] = $info["number"]["number"].$s2["delimeter"].($info["number"]["maxline"]+$i+1);
                            }
                            numberMT::save($db->QueryInsert("v_sip", $s2), $lineNumberMt);
                        }

                    }elseif(count($info["lines"]) > $update["lines"]){
                        $update["lines"] = count($info["lines"]);
                    }
                }

                foreach($info["all"] as $id)
                {
                    $u = $update;
                    $u["id"] = $id;

                    if($info["number"]["id"] == $id)
                        unset($u["number"]);

                    $db->QueryUpdate("v_sip", array("id", "client_id"), $u); 
                }


            }elseif($s["atype"] == "link"){

                if($numbersMT)
                {
                    numberMT::save($s["id"], $numbersMT);
                    self::recalcCallCount($s["id"]);
                }
            }else{
                $db->QueryUpdate("v_sip", array("id", "client_id"), $update);

            }
            vNotify::anonse("sip:modify:".$update["id"]);

        }

        if($updateNum)
        {
            $db->QueryUpdate("v_number_mt", "sip_id", $updateNum);

            vNotify::anonse("sip:modify:".$updateNum["sip_id"]);

            if($s["atype"] == "number" && $s["type"] == "line")
            {
                foreach($db->AllRecords("select id from v_sip where parent_id = '".$updateNum["sip_id"]."'") as $l)
                {
                    numberMT::save($l["id"], $updateNum["number_id"]."=".$updateNum["direction"]);
                    vNotify::anonse("sip:modify:".$l["id"]);
                }
            }
        }

        return false;
    }


    public static function recalcCallCount($id, $upId = null)
    {
        //define("voip_debug", 1);

        l::ll(__CLASS__,__FUNCTION__, $id);

        if($upId === null)
            $upId = self::_getParentId($id);

        if($upId)
        {
            $ids = self::_getDepTrunks($upId);
        }else{
            $ids = array($id);
        }

        $ids[] = $upId;


        $count = 0;
        foreach($ids as $id)
        {
            $numList = self::_getTrunkNumbers($id);
            $countCalls = self::_getCallCountInNumbers($numList);
            if($id != $upId)
                self::_saveCallCount($id, $countCalls);
            $count += $countCalls;
        }
        if($upId)
            self::_saveCallCount($upId, $count, false);

        //exit();
    }
    private static function _getParentId($id)
    {
        l::ll(__CLASS__,__FUNCTION__, $id);
        global $db;
        return $db->GetValue("select parent_id from v_sip where id ='".$id."'");
    }

    private static function _getDepTrunks($id)
    {
        l::ll(__CLASS__,__FUNCTION__, $id);
        global $db;
        $d = array();

        foreach($db->AllRecords("Select id from v_sip where parent_id = '".$id."'") as $l)
            $d[] = $l["id"];

        return $d;
    }

    private static function _getTrunkNumbers($id)
    {
        l::ll(__CLASS__,__FUNCTION__, $id);
        global $db;

        $r = $db->GetRow("select atype, number from v_sip where id = '".$id."'");
        if($r["atype"] == "link")
        {
            $ns = array();
            foreach($db->AllRecords("select number_id from v_number_mt where sip_id = '".$id."'") as $l)
                $ns[] = $l["number_id"];

            return $ns;
        }else{
            return array($r["number"]);
        }
    }

    private static function _getCallCountInNumbers($ns)
    {
        l::ll(__CLASS__,__FUNCTION__, $ns);
        global $db;
        return $db->GetValue("select sum(call_count) from v_number where id in ('".implode("','", $ns)."') and enabled='yes'");
    }

    private static function _saveCallCount($id, $cc, $widthParent = true)
    {
        l::ll(__CLASS__,__FUNCTION__, $id, $cc, $widthParent);
        global $db;

        $db->QueryUpdate("v_sip", "id", array(
                    "id" => $id,
                    "call_count" => $cc
                    )
                );

        if($widthParent)
            $db->QueryUpdate("v_sip", "parent_id", array(
                        "parent_id" => $id,
                        "call_count" => $cc
                        )
                    );
    }
        


    private static function passGen()
    {
        $s = "";
        for($i=1;$i<=12;$i++)
        {
            $r = rand(1,36);
            $s .= $r > 26 ? ($r-26-1) : chr(97+$r-1);
        }
        return $s;
    }

    private static function normalizeClient($fixclient)
    {
        $numberMT = $fixclient;
        if(preg_match_all("@^([a-z0-9A-Z]+)/\d+$@", $fixclient, $o))
        {
            $numberMT = $o[1][0];
        }
        return $numberMT;
    }

    private static function getMaxLinePrefInClient($mask)
    {
        global $db;

        return $db->GetValue("select max(line_pref) from v_sip where ".sqlClient()." and line_mask='".$mask."'");
    }


    private static function getMaxLinePref($parentId)
    {
        global $db;

        return $db->GetValue("select max(line_pref) from v_sip where parent_id = '".$parentId."'");
    }

    public static function logPasswordView($s, $event = "view")
    {
            global $user, $db;

            $db->QueryInsert("log_password",array(
                        "time" => array("NOW()"),
                        "number" => $s["number"],
                        "sip_id" => $s["id"],
                        "user_id" => $user->Get("id"),
                        "client_id" => $s["client_id"],
                        "event" => $event
                        ));
    }

}



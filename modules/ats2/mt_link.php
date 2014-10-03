<?php


class MTLink
{
    function __construct($fixclient, $action, $id = 0)
    {
        $this->edit($id);
    }

    function edit($id)
    {

        if(get_param_raw("cancel", "") != "") {
            header("Location: ./?module=ats2&action=mt");
            exit();
        }

        global $design, $fixclient;

        $id= get_param_integer("id", 0);

        $data = mtLinkDB::get($id);

        $map = $this->getMap($id);

        include_once INCLUDE_PATH."formconstructor.php";
        $formConstr = new FormConctructor($map);

        $error = "";

        if(get_param_raw("do", ""))
        {
            $gData = $formConstr->gatherFromData($id);

            $gData["id"] = $id;
            
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

                $this->setUpdateClientsByMultitrunkId($gData["multitrunk_id"]);

                ats2mt::updatePoolCount($id);

                header("Location: ./?module=ats2&action=mt");
                exit();
            }
        }else{
            $formConstr->make($data);
        }

        $design->assign("error", $error);
        $design->assign("data", $data);

        $design->AddMain("ats2/multitrunk_link_edit.htm");
    }

    private function apply(&$data)
    {
        if($data["id"] == 0)
        {
            unset($data["id"]);

            return mtLinkDB::insert($data);
        }else{
            mtLinkDB::update($data);
        }
    }

    private function getMap($id)
    {

        $m = array();

        $m["multitrunk_id"] = array(
                "type" => "select",
                "title" => "Мультитранк",
                "data" => array(array("type" => "query", "query" => "select id, name from a_multitrunk where parent_id = 0 order by name", "db" => "db_ats"))
                );

        $m["numbers_mt"] = array(
                "title" => "Номера",
                "type" => "sort_list",
                "data_all" => array(array("type" => "query", "query" => "
                        select id, concat(number, 'x', call_count) as number 
                        from a_number 
                        where ".sqlClient()." 
                        and id not in ( #not in linked, except self binding
                            select number_id 
                            from a_link l, a_number n 
                            where 
                            l.number_id =n.id 
                            and ((n.".sqlClient()." )
                                and !(c_type ='multitrunk' and c_id = ".$id."))
                            )
                        and id not in ( # not in virtpbx
                            select type_id
                            from a_virtpbx_link l, a_virtpbx v
                            where 
                                    l.virtpbx_id = v.id
                                and v.".sqlClient()."
                            )

                        order by number
                        ", "db" => "db_ats"))
                );


        return $m;
    }

    private function check(&$data, $id)
    {
        // пррверка возможна только на номера клиента, но так же возмодно использование транка по другому, без номеров...
        return ;


        include INCLUDE_PATH."checker.php";
        checker::isEmpty($data["numbers_mt"], "Номера не заданы!");
    }

    private function setUpdateClientsByMultitrunkId($id)
    {
        global $db_ats;

        foreach($db_ats->AllRecords("SELECT distinct client_id FROM `a_multitrunk` where ".$id." in (id, parent_id)") as $r)
        {
            ats2sync::updateClient($r["client_id"]);
        }
    }

}

class mtLinkDB
{
    public function get($id)
    {
        if($id == 0)
        {
            $c = getClient();
            $data = array(
                    "id"            => 0,
                    "multitrunk_id" => 0,
                    "numbers_mt"    => ""
                    );
        }else{
            global $db_ats;
            $data = $db_ats->GetRow($q = "select id, parent_id as multitrunk_id from a_multitrunk where ".sqlClient()." and id='".$id."'");

            //$data = $db_ats->GetRow("select id from a_multitrunk m, a_connect c where c.id = m.c_id and m.id = ".$id);

            $mts = "";
            foreach($db_ats->AllRecords(
                        "select number_id
                        from a_link 
                        where c_type='multitrunk' and c_id='".$id."'") as $l)
            {
                $mts .= ($mts ? "," : "").$l["number_id"];
            }

            $data["numbers_mt"] = $mts;
        }

        return $data;
    }

    public function insert($d)
    {
        global $db_ats;

        $d["numbers_mt"] = trim($d["numbers_mt"]);
        if(!$d["numbers_mt"]) return false;

        $id = $db_ats->QueryInsert("a_multitrunk", array(
                    "name" => $db_ats->GetValue("select name from a_multitrunk where id ='".$d["multitrunk_id"]."'"),
                    "parent_id" => $d["multitrunk_id"],
                    "client_id" => getClientId()
                    )
                );

        self::save_numbers($id, $d["numbers_mt"]);

        //self::checkForCombinationLink();

        return $id;
    }

    private function save_numbers($id, $numbers, $whithDel = true)
    {
        global $db_ats;


        if($whithDel)
            $db_ats->QueryDelete("a_link", array(
                        "c_type" => "multitrunk",
                        "c_id" => $id
                        ));

        $numbers = trim($numbers);
        if(!$numbers) return;

        foreach(explode(",", $numbers) as $numberId)
        {
            $db_ats->QueryInsert("a_link", array(
                        "c_type" => "multitrunk",
                        "c_id" => $id,
                        "number_id" => $numberId
                        ));
        }
    }

    public function update($d)
    {
        global $db_ats;

        $n = self::get($d["id"]);

        $id = $n["id"];
        $cId = $n["c_id"];

        $dn = array_diff_assoc($d, $n);

        if($dn)
        {
            if(isset($dn["multitrunk_id"]))
                $db_ats->QueryUpdate("a_multitrunk", "id", array(
                            "id" => $n["id"],
                            "parent_id" => $dn["multitrunk_id"],
                            "name" => $db_ats->GetValue("select name from a_multitrunk where id ='".$d["multitrunk_id"]."'")
                            ));

            if(isset($dn["numbers_mt"]))
                self::save_numbers($n["id"], $dn["numbers_mt"]);

            self::checkForCombinationLink();
        }

        return /*$db_ats->AffectedRows() ? $data["id"] :*/ 0;
    }
    public function checkForCombinationLink()
    {
        global $db_ats;

        // combination
        foreach($db_ats->AllRecords(
                    "SELECT distinct 
                        parent_id as trunk_id, min(id) as min_id, max(id) max_id, count(1) c
                        FROM `a_multitrunk` 
                        WHERE parent_id != 0 
                        GROUP BY parent_id 
                        HAVING c > 1") as $l)
        {
            $db_ats->Query("update a_link set c_id = '".$l["min_id"]."' where c_id = '".$l["max_id"]."' and c_type ='multitrunk'");
            $db_ats->QueryDelete("a_multitrunk", array("id" => $l["max_id"]));
        }

        //del empty
        foreach($db_ats->AllRecords("SELECT 
                    id, (
                        select count(*) 
                        from a_link l 
                        where 
                                l.c_id = m.id 
                            and c_type = 'multitrunk') as count 
                    FROM `a_multitrunk` m 
                    where m.parent_id !=0 
                    having count =0") as $l)
        {
            $db_ats->QueryDelete("a_multitrunk", array("id" => $l["id"]));
        }
    }
}




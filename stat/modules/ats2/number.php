<?php




class aNumber
{
    public function edit()
    {
        global $design;

        $id = get_param_integer("id", 0);
        if(!$id) {trigger_error2("Номер не найден"); return;}

        $n = self::getNumber($id);
        if(!$n) {trigger_error2("Номер не найден"); return;}

        list($isEdit, $l) = self::getNumberLink($id);


        if(get_param_raw("cancel", "")) //cancel key
        {
            header("Location: ./?module=ats2");
            exit();
        }

        include_once INCLUDE_PATH."formconstructor.php";

        $l["id"] = $n["id"];
        $l["number"] = $n["number"];

        if($isEdit){
            $map = self::getMap($n["id"], true, $l["c_type"]);
        }else{
            $map = self::getMap($n["id"], false);
        }

        $formConstructor = new FormConctructor($map);

        $error = "";
        if(get_param_raw("do", "")) //save key
        {
            $gData = $formConstructor->gatherFromData();

            try{
                self::checkSavedNumber($gData);
            }catch(Exception $e)
            {
                $error = $e->getMessage();
            }

            if($error)
            {
                $gData["id"] = $l["id"];
                $formConstructor->make($gData);
            }else{
                self::save($l, $gData, $isEdit);
                ats2sync::updateClient($n["client_id"]);
                header("Location: ./?module=ats2");
                exit();
            }
        }else{

            if($l)
            {
                $l = self::toMake($l);
            }

            $formConstructor->make($l);
        }

        $design->assign("error", $error);
        $design->AddMain("ats2/number_edit.htm");
    }

    private function checkSavedNumber(&$gData)
    {
        $type = $gData["c_type"];
        $numbers = explode(",", $gData["c_id_".$type]);

        if ($numbers)
        {
            global $db_ats;
            if ($db_ats->GetValue("select count(*) from a_link where c_id in ('".implode("','", $numbers)."') and c_type in ('line', 'trunk') and number_id != '".self::getNumberId($gData["number"])."'"))
            {
                throw new Exception("Прикрепляемая линия уже прикреплена у другому номеру");
            }
        }
    }

    private function save($l, $gData, $isEdit)
    {
        $gData = self::fromForm($gData);

        $gData["id"] = $l["id"];

        self::_saveUpdate($gData);

    }

    private function _saveUpdate($l)
    {
        global $db_ats;

        //load disabled 
        $disabled = array();
        if($l["c_id"])
            $disabled = $db_ats->AllRecords(
                    "select l.c_id 
                    from a_link l, a_number n 
                    where l.number_id = n.id 
                        and enabled = 'no' 
                        and l.c_id in ('".implode("','", $l["c_id"])."') 
                        and c_type in ('line', 'trunk')", "c_id");

        if($disabled)
            $db_ats->Query("delete from a_link where c_id in ('".implode("','", array_keys($disabled))."') and c_type in ('line', 'trunk')");

        $db_ats->QueryDelete("a_link", array("number_id" => $l["id"]));

        $idx = 1;
        foreach($l["c_id"] as $cId)
        {
            $db_ats->QueryInsert("a_link", 
                array(
                    "c_type" => $l["c_type"],
                    "c_id" => $cId,
                    "number_id" => $l["id"]
                )
            );

            $db_ats->QueryUpdate("a_line", "id", 
                array(
                    "id" => $cId, 
                    "priority" => $idx++
                )
            );
        }
    }

    private function fromForm($d)
    {
        if($d["c_type"] == "line")
        {
            $cId = array();
            foreach(explode(",", $d["c_id_line"]) as $l)
                if($l)
                    $cId[] = $l;
        }else{
            $cId = array($d["c_id_trunk"]);
        }
        unset($d["c_id_trunk"], $d["c_id_line"]);

        $d["c_id"] = $cId;

        return $d;
    }

    public function del($numberId = null)
    {
        global $db_ats;

        if (is_null($numberId))
        {
		$numberId = get_param_integer("id", 0);
        }
        $number = self::getNumber($numberId);

        if(!$numberId || !$number) 
        {
            trigger_error2("Ошибка удаления");
            return;
        }
        
        list($isEdit, $l) = self::getNumberLink($numberId);

        if(!$isEdit)
        {
            trigger_error2("Ошибка удаления");
            return;
        }

        $db_ats->QueryDelete("a_link", array("number_id" => $numberId));
        
        ats2sync::updateClient($number["client_id"]);

        header("Location: ./?module=ats2");
        exit();
    }
    public function bulk_del($ids)
    {
        global $db_ats;
        foreach ($ids as $numberId => $c_ids)
        {
		$number = self::getNumber($numberId);
		if (self::delete_alink_rows($numberId, $number, $c_ids) === false)
		{
			trigger_error2("Ошибка удаления");
			continue;
		}
        }
        ats2sync::updateClient($number["client_id"]);
        header("Location: ./?module=ats2");
        exit();
    }
    public function delete_alink_rows($numberId, $number, $c_ids)
    {
	global $db_ats;
	if(!$numberId || !$number) 
	{
		return false;
	}
	
	list($isEdit, $l) = self::getNumberLink($numberId);

	if(!$isEdit)
	{
		return false;;
	}

	foreach ($c_ids as $v) 
	{
		$db_ats->QueryDelete("a_link", array("number_id" => $numberId, 'c_id' => $v));
	}
	return true;
	
    }

    public function getNumber($id)
    {
        global $db_ats;
        
        $n = $db_ats->GetRow("select * from a_number where id = '".$id."' and enabled='yes'");

        return $n;
    }

    private function getNumberId($number)
    {
        global $db_ats;
        
        return $db_ats->GetValue("select id from a_number where number = '".$number."' and ".sqlClient());
    }

    private function getNumberLink($numberId)
    {
        global $db_ats;
        
        $n = $db_ats->GetRow("select
                c_type,
                group_concat(ln.c_id order by l.priority, ln.id) as c_id
                from a_link ln, a_line l
                where ln.c_id = l.id and number_id = '".$numberId."'
                group by number_id");

        if(!$n)
            return array(false, array("c_type" => "line", "c_id" => ""));

        return array(true, $n);
    }

    private function toMake($l)
    {
        $l["c_id_line"] = $l["c_id_trunk"] = 0;
        $l["c_id_".$l["c_type"]] = $l["c_id"];
        return $l;
    }

    private function getMap($numberId, $isEdit = false, $type = "line")
    {
        $map = array();

        $map["number"] = array("title" => "Номер", "type" => "info");

        if($isEdit)
        {
            $map["c_type"] = array(
                    "title" => "Тип соединения",
                    "type" => "info",
                    );
        }else{
            $map["c_type"] = array(
                    "title" => "Тип соединения",
                    "type" => "radio",
                    "data" => array(array("type" => "array", "array" => array("line" => "Линия", "trunk" => "Транк"))),
                    );
        }

        $sqlAccounts = array(array("type" => "query", "query" => "
                    select 
                        al.id, al.account 
                    from 
                        a_line al 
                    where 
                            al.client_id = '".getClientId()."' 
                        and is_group = 0

                        /* not in used sip accounts */
                        and id not in ( 
                                SELECT c_id 
                                FROM `a_link` k, a_number n 
                                where 
                                        n.client_id = al.client_id 
                                    and n.id = k.number_id 
                                    and n.id != '".$numberId."'
                                )
                        
                        /* not in virtpbx accounts */
                        and id not in (
                                select type_id 
                                from a_virtpbx_link l, a_virtpbx v 
                                where 
                                        l.virtpbx_id = v.id 
                                    and v.client_id = al.client_id 
                                    and type='account'
                                )
                    ORDER BY account",
                    "db" => "db_ats")
                );

                
        $map["c_id_trunk"] = array(
                "title" => "Транк",
                "type" => "select",
                "data" => $sqlAccounts,
                "condition" => array("eq", "c_type", "trunk")
                );

                            /*.
                        ($isEdit ? "union 
                         select 
                         n.id,account 
                         from a_line n, a_link l 
                         where 
                                l.c_id = n.id 
                            and ".sqlClient()." 
                            and l.number_id = ".$numberId : "")*/

        $map["c_id_line"] = array(
                "title" => "Линии",
                "type" => "sort_list",
                "data_all" => $sqlAccounts,
                "condition" => array("eq", "c_type", "line")
                );

        return $map;
    }

}

<?php




class aVirtPbx
{


    public function edit()
    {
        global $design;

        $virtpbx = virtpbx::getList();

        if(get_param_raw("cancel", "")) //cancel key
        {
            header("Location: ./?module=ats2");
            exit();
        }

        include_once INCLUDE_PATH."formconstructor.php";

        $map = self::getMap($virtpbx["id"]);

        $formConstructor = new FormConctructor($map);

        $error = "";
        if(get_param_raw("do", "")) //save key
        {
            $gData = $formConstructor->gatherFromData();

            try{
                self::checkSavedData($gData);
            }catch(Exception $e)
            {
                $error = $e->getMessage();
            }

            if($error)
            {
                $formConstructor->make($gData);
            }else{

                if (($result = self::save($gData)) === true)
                {
                    header("Location: ./?module=ats2");
                    exit();
                } else { // same error

                    $virtpbx = virtpbx::getList();
                    $l = array(
                            "numbers"  => implode(",", array_keys($virtpbx["numbers"]))
                            );

                    $formConstructor->make($l);
                    //$formConstructor->make($gData);

                    $design->assign("error_full", $result);
                    $design->AddMain("ats2/virtpbx_error_save.htm");
                }
            }
        } else {
            $ll = array();
            foreach($virtpbx["numbers"] as $n)
            {
                $ll[] = $n["id"]."=".$n["direction"];
            }
            $l = array(
                    "numbers"  => implode(",", $ll)
                    );
            $formConstructor->make($l);
        }

        $design->assign("error", $error);
        $design->AddMain("ats2/virtpbx_edit.htm");
    }

    private function checkSavedData($d)
    {
        global $db_ats;

        $numbers = array();

        foreach ($db_ats->AllRecords("
                    SELECT 
                        c_id as account_id, 
                        number_id 
                    FROM 
                        `a_link` l, a_number n 
                    WHERE 
                            n.client_id = '".getClientId()."' 
                        and n.id = l.number_id") as $l)
        {
            if ($l["number_id"])
                if (!isset($numbers[$l["account_id"]]))
                    $numbers[$l["number_id"]] = 1;
        }

        foreach(explode(",", $d["numbers"]) as $fNumber)
        {
            if (!$fNumber) continue;

            list($numberId, ) = explode("=", $fNumber);

            if (isset($numbers[$numberId]))
                throw new exception("Один из номера уже используется");
        }

    }

    private function save($data)
    {
        global $db_ats;

        $virtPbxId = self::getVirtPbxId();

        if (!$virtPbxId)
            throw new Exception("Виртуальная АТС не найдена у клиента!");

        $vpbx = virtPbx::getList();

        $getNumbers = array();
        foreach(explode(",", $data["numbers"]) as $fNumber)
        {
            list($numberId, $direction) = explode("=", $fNumber);
            $getNumbers[$numberId] = $direction;
        }

        $savedNumbers = array();
        foreach($vpbx["numbers"] as $numberId => $oNumber)
        {
            $savedNumbers[$numberId] = $oNumber["direction"];
        }



        $saved = array_keys($savedNumbers);
        $posted = array_keys($getNumbers);

        $add = array_diff($posted, $saved);
        $del = array_diff($saved, $posted);

        $clientId = getClientId();


        $result = array();
        $isErrorResult = false;

        // need add number 
        if ($add)
        {
            foreach($add as $numberId)
            {
                $isError = false;
                $code = 0;
                $number = ats2Numbers::getNumberById($clientId, $numberId);

                if (!$vpbx["is_started"])
                {
                    virtPbx::addNumber($clientId, $number, $getNumbers[$numberId]);
                } else {

                    try{
                        $res = ApiVpbx::addDid($clientId, $phone, $getNumbers[$numberId]);
                    }catch(Exception $e)
                    {
                        $res = $e->getMessage();
                        $code = $e->getCode();
                        $isErrorResult = $isError = true;
                    }

                    $result[] = array("number" => $number, "action" => "add", "error" => $isError, "message" => $res, "code" => $code);
                }
            }                
        }

        // need remove number
        if ($del)
        {
            foreach($del as $numberId)
            {
                $isError = false;
                $code = 0;
                $number = ats2Numbers::getNumberById($clientId, $numberId);

                if (!$vpbx["is_started"])
                {
                    virtPbx::delNumber($clientId, $number);
                } else {
                    try{
                        $res = ApiVpbx::delDid($clientId, $number);
                    }catch(Exception $e)
                    {
                        $res = $e->getMessage();
                        $code = $e->getCode();

                        $isErrorResult = $isError = true;
                    }

                    $result[] = array("number" => $number, "action" => "del", "error" => $isError, "message" => $res, "code" => $code);
                }
            }                
        }

        //need change direction
        $isChangedDirection = false;
        foreach($getNumbers as $numberId => $direction)
        {
            if (isset($savedNumbers[$numberId]) && $savedNumbers[$numberId] != $direction) 
            {
                virtPbx::changeDirection($clientId, $numberId, $direction);
                $isChangedDirection = true;
            }
        }

        if ($add || $del || $isChangedDirection) //is need client sync schema
        {
            ats2sync::updateClient($clientId);
        }



        if ($isErrorResult)
        {
            return $result;
        }

        return true;
    }


    private function getMap($id)
    {
        $map = array();


        $sqlNumbers = array(array("type" => "query", "query" => 
                    "select id, concat(number, 'x', call_count), number from a_number where id not in (select distinct number_id from a_link) and client_id = '".getClientId()."' order by number",
                    "db" => "db_ats"
                    )
                );

        $ap["numbers"] = array(
                "title" => "Номера",
                "type" => "sort_list",
                "data_all" => $sqlNumbers
                );

        $map["numbers"] = array(
                "title" => "Номера",
                "type" => "multitrunk_numbers",
                "data_all" => $sqlNumbers
                );

        //printdbg($m);

        global $design;

        $design->assign("direction", array(
                    "full" => "Full",
                    "russia" => "Russia",
                    "mskmob" => "MskMob",
                    "msk" => "Msk"
                    )
                );

        return $map;
    }

    private function getVirtPbxId()
    {
        global $db_ats;
        
        return $db_ats->GetValue("select id from a_virtpbx where client_id = '".getClientId()."'");
    }

}

<?php

class FormConctructor
{

    private $map = array();

    function FormConctructor($map)
    {
        $this->map = &$map;
    }

    function make($data)
    {
        global $design;

        $constructor = array("list" => array(), "selected" => array());

        $aCondValues = array();

        foreach($this->map as $name => &$value)
        {
            // make data
            //if(!isset($value["type"])) { printdbg($value,$name); }
            if (in_array($value["type"], array("select", "radio", "checkbox", "number_lines_select", "is_pool")))
            {
                $constructor["list"][$name] = $this->_constructor_makeData($value["data"]);

                if (isset($data[$name]))
                {
                    $selected = $data[$name];
                }elseif (($selected = get_param_raw($name, false)) === false) 
                {
                    if(isset($value["default"]))
                    {
                        $selected = $value["default"];
                    }
                }
                if ($value["type"] == "checkbox" || $value["type"] == "is_pool")
                {
                    if (!empty($selected))
                    {
                        $selected = explode(",", $selected);
                    }
                }
                $constructor["selected"][$name] = $selected;
            }elseif($value["type"] == "sort_list")
            {
                $allData = $this->_constructor_makeData($value["data_all"]);

                $count = count($allData);
                $inData = array();

                if (!isset($value["data_in"]) || empty($value["data_in"]))
                {
                    $value["data_in"] = $data[$name];
                }
                foreach(explode(",", $value["data_in"]) as $v)
                {
                    if (isset($allData[$v]))
                    {
                        $inData[$v] = $allData[$v];
                        unset($allData[$v]);
                    }
                }

                $constructor["list"][$name] = array("used" => $inData, "noused" => $allData, "count" => $count);
            }elseif($value["type"] == "multitrunk_numbers")
            {
                $numbersData = $this->_constructor_makeData($value["data_all"]);

                $noused =$numbersData;
                $used = array();
                if($data[$name])
                {
                    foreach(explode(",",$data[$name]) as $l)
                    {
                        list($l_id, $directionId)=explode("=", $l);
                        if(isset($numbersData[$l_id]))
                        {
                            $used[$l_id."=".$directionId] = $numbersData[$l_id]." - ".$directionId;
                            unset($noused[$l_id]);
                        }
                    }
                }
                $constructor["list"][$name] = array("used" => $used, "noused" => $noused);

            }elseif($value["type"] == "permit_net")
            {
                $data[$name] = $data[$name] == "" ? array() : explode(",", $data[$name]);
            }


            // process condition
            if (isset($value["condition"]))
            {
                $this->addConditionValues($aCondValues, $value["condition"]);
                $value["condition_js"] = $this->makeJSCondition($value["condition"]);
            }
        }

        $constructor["check_items"] = array_unique($aCondValues);
        foreach($constructor["check_items"] as $item)
        {
            $this->map[$item]["check_change"] = true;
        }

        $design->assign("data", $data);
        $design->assign("constructor", $constructor);
        $design->assign("map", $this->map);
    }

    function makeJSCondition($cond)
    {
        if (is_array($cond[1]) && is_array($cond[2]))
        {
            $cond[1] = $this->makeJSCondition($cond[1]);
            $cond[2] = $this->makeJSCondition($cond[2]);
        }

        switch ($cond[0])
        {
            case 'eq': $cond[0] = "=="; break;
            case 'nq': $cond[0] = "!="; break;
            case 'or': $cond[0] = "||"; break;
            case 'and': $cond[0] = "&&"; break;
        }

        if ($cond[0] == "==" || $cond[0] == "!=")
        {
            return "(v_".$cond[1]." ".$cond[0]." '".$cond[2]."')";
        }else{
            return "(".$cond[1]." ".$cond[0]." ".$cond[2].")";
        }
    }

    function addConditionValues(&$aVals, $cond)
    {
        // depth
        if (is_array($cond[1]) && is_array($cond[2]))
        {
            $this->addConditionValues($aVals, $cond[1]);
            $this->addConditionValues($aVals, $cond[2]);
        }

        if (!is_bool($cond[1]))
        {
            if ($cond[0] == "eq" || $cond[0] == "nq")
            {
                $aVals[] = $cond[1];
            }
        }
        return false;
    }

    function _constructor_makeData($value)
    {
        global $db;
        $curData = array();
        foreach($value as $val) 
        {
            if ($val["type"] == "array")
            {
                foreach($val["array"] as $k => $v)
                {
                    $curData[$k] = $v;
                }
            }elseif($val["type"] == "query")
            {
                foreach($db->AllRecords($val["query"], "", MYSQL_BOTH) as $v)
                {
                    $curData[$v[0]]= $v[1];
                }
            }elseif($val["type"] == "sql")
            {
                foreach($db->AllRecords($val["sql"]) as $v)
                {
                    $curData[$v["id"]]= $v;
                }
            }

        }
        return $curData;
    }
    
    function gatherFromData()
    {
        $data = array();
        foreach($this->map as $name => $item)
        {
            if($item["type"] == "break") continue;

            $data[$name] = get_param_raw($name, isset($item["default"]) ? $item["default"] : "");

            if(!is_array($data[$name]))
            {
                $data[$name] = trim($data[$name]);
            }

            if ($item["type"] == "checkbox" || $item["type"] == "is_pool")
            {
                if (is_array($data[$name]))
                {
                    $data[$name] = implode(",", $data[$name]);
                }
            }elseif($item["type"] == "sort_list")
            {
                $data[$name] = str_replace(":", ",", substr(get_param_raw("send_members_".$name, ""),0,-1));
            }elseif($item["type"] == "permit_net" || $item["type"] == "multitrunk_numbers")
            {
                if($item["type"] == "multitrunk_numbers")
                {
                    $permit = get_param_raw("mtn_save","");
                }else{
                    $permit = get_param_raw("permit_net_save_".$name,"");
                }
                $p = array();
                if($permit)
                {
                    foreach(explode(":", $permit) as $l)
                    {
                        $p[$l] = $l;
                    }
                }
                $data[$name] = $p ? implode(",", $p) : "";
            }elseif($item["type"] == "number_lines_select")
            {
                $data[$name] = get_param_raw("number_lines_".$name, 0);
            }
        }
        return $data;
    }

}


function calcCondition($cond, $data)
{
    return checkConditionValue($cond, $data);
}

function checkConditionValue($conf, $data)
{

    $sign = $conf[0];   // operation
    $value1 = $conf[1]; // var name
    $value2 = $conf[2]; // var value

    if (is_array($value1) && is_array($value2))
    {
        // call depth
        $value1 = checkConditionValue($value1, $data);
        $value2 = checkConditionValue($value2, $data);
    }

    if(is_bool($value1) && is_bool($value2))
    {
        // calc bool
        switch($sign)
        {
           case "eq": return $value1 == $value2; break;
           case "nq": return $value1 != $value2; break;
           case "or": return $value1 || $value2; break;
           case "and": return $value1 && $value2; break;
        }
        return false;
    }else{
        // calc value
        if (isset($data[$value1]))
        {
            $varVal = $data[$value1];
            switch($sign)
            {
                case 'eq' : return $varVal == $value2; break;
                case 'nq' : return $varVal != $value2; break;
            }
        }
        return false;
    }
}

<?php


class historyViewAction
{
    public function check($clientId)
    {
        $isNeedRedirect = false;

        if($verId = get_param_integer("del_section", "0"))
        {
            historyViewAction::delSection($verId);
            $isNeedRedirect = true;
        }

        if($vId = get_param_integer("del_value", "0"))
        {
            historyViewAction::delValue($vId);
            $isNeedRedirect = true;
        }

        if($verId = get_param_integer("del_apply", 0))
        {
            historyViewAction::clearApplyTS($verId);
            $isNeedRedirect = true;
        }

        if($verId = get_param_integer("add_apply", 0))
        {
            historyViewAction::setApplyTS($verId, get_param_raw("date"));
            $isNeedRedirect = true;

        }

        if(
                ($verId = get_param_integer("fs", 0))
                && ($ff = get_param_raw("ff", array()))
          )
        {
            historyViewAction::moveValuesToVersion($verId, $ff);
            $isNeedRedirect = true;
        }

        if($vId = get_param_integer("apply_value", 0))
        {
            historyViewAction::applyValue($vId, $clientId);
            $isNeedRedirect = true;
        }
        
        if($isNeedRedirect)
        {
            header("Location: ./?module=clients&id=".$clientId."&action=view_history");
            exit();
        }
    }

    private function delValue($fId)
    {
        global $db;

        $db->Query("delete from log_client_fields where id = '".$fId."'");
    }

    private function delSection($verId)
    {
        global $db;

        $db->Query("delete from log_client where id = '".$verId."'");
        $db->Query("delete from log_client_fields where ver_id = '".$verId."'");
    }

    private function clearApplyTS($verId)
    {
        global $db;

        $db->Query("update log_client set apply_ts = '0000-00-00' where id = '".$verId."'");
    }

    private function setApplyTS($verId, $date)
    {
        global $db;

        list($year,$month,$day) = explode("-", $date."---");
        $db->Query("update log_client set apply_ts = '".$year."-".$month."-".$day."' where id = '".$verId."'");
    }

    private function applyValue($id, $clientId)
    {
        global $db;

        $v = $db->GetRow("select field, value_to from log_client_fields where id = '".$id."'");

        if($v)
            $db->QueryUpdate("clients", "id", array("id" => $clientId, $v["field"] => $v["value_to"]));
    }

    private function moveValuesToVersion($verId, $vIds)
    {
        global $db;

        $db->Query("update log_client_fields set ver_id = '".$verId."' where id in ('".implode("','", $vIds)."')");
    }
}

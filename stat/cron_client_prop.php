<?
	define('NO_WEB',1);
	define('NUM',35);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf_yii.php";
	include MODULES_PATH.'clients/module.php';

    //echo $db->GetValue("select database()");

    $data = date("Y-m-d");
    //$data = "2012-04-01";

    foreach($db->AllRecords(
                "select id, client_id
                from log_client 
                where 
                        apply_ts <= '".$data."' 
                    and is_apply_set ='no' 
                    and is_overwrited = 'no'
                order by id") as $l)
{
    $ff = array("id" => $l["client_id"]);
    foreach($db->AllRecords(
                "select * 
                from log_client_fields 
                where ver_id = '".$l["id"]."'
                order by id") as $f)
    {
        $ff[$f["field"]] = $f["value_to"];        
    }

    $db->QueryUpdate("clients", "id", $ff);
    $db->QueryUpdate("log_client", "id", array(
                "id" => $l["id"],
                "is_apply_set" => "yes"
                ));


}


    //ClientCS::exportClient(9130);
 

<?
	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'../');
	define('NO_WEB',1);
	include PATH_TO_ROOT."conf.php";

    $id = get_param_protected("id","0");

                                                                                                      
    header('Content-type: text/html; charset=utf-8');
    if($id)
{
    foreach($db->AllRecords("select type, data, is_active, is_official from client_contacts where client_id = '".$id."'") as $u)
    echo "\n<br>".$u["type"]."|||".$u["data"]."|||".$u["is_active"]."|||".$u["is_official"];
}



?>

<?
	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'../../stat/');
	define('NO_WEB',1);
	include PATH_TO_ROOT."conf_yii.php";

    $login = get_param_protected("login","");
    $pwd = get_param_protected("pwd", "");

                                                                                                      
    header('Content-type: text/html; charset=utf-8');
    if($login && $pwd)
{
    $u = $db->GetRow($q="select * from clients where client='".$login."' and md5(password) = '".$pwd."'");
    if($u)
    {
        $e = $db->GetRow("select data from client_contacts where client_id = '".$u["id"]."' and type='email' order by  is_active desc, is_official desc, id desc limit 1");
    echo $u["id"]."|||".$u["company"]."|||".($e?$e["data"]: "");
    exit();
    }
}

echo "id:0";


?>

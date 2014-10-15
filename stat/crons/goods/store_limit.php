<?php

define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);


include PATH_TO_ROOT."conf_yii.php";

$users = GoodNotificationLimits::getAllLimits();

foreach ($users as $user_id => $data)
{
    $goods = array();
    foreach ($data as $v)
    {
        if ($v->qty_free < $v->limit_value)
        {
            $v->qty_free = ($v->qty_free) ? $v->qty_free : 0;
            $goods[] = "Позиция (id: ".$v->num_id.") ". $v->name .". Количество на складе '" . $v->store_name . "' ниже ".$v->limit_value.", составляет: ".$v->qty_free;
        }
    }
 
    if (!empty($goods))
    {
        $headers = "Content-type: text/html; charset=utf-8";
        $subject = "оповещение о снижении минимального остатка на складе";
        $body = "Количество по следующим позициям меньше установленного уровня:<br><br>".implode("<br>", $goods);

        mail($v->email, $subject, $body , $headers);
    }
}

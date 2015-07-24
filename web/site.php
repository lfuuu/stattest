<?php
header("Content-type: text/html; charset=utf-8");
?>
<html lang="ru">
<head>
    <meta charset="utf-8"/>
</head>
<body>
<form action="/operator/service.php" method=get>
    <input type=hidden name=action value=add_client>

    Компания: <input type=text name=company value="ООО Первая компания"><br>
    Телефон: <input type=text name=phone value=123456><br>
    Email: <input type=text name=email value="adima123@yandex.ru"><br>
    Коментарий: <input type=text name=client_comment value="нужен номер"><br>
    ФИО: <input type=text name=fio value="Клиент Клиент Клиентович"><br>
    Доступ в ЛК: <input type=checkbox name=lk_access value=1><br>
    <input type=submit>
</form>
</body>
</html>
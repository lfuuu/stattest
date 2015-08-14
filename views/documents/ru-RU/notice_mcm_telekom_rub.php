<?php

use app\classes\BillContract;

$contract = BillContract::getString($document->bill->clientAccount->id, time());

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Уведомление о передаче прав и обязанностей по договору: </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>

    <body bgcolor="#FFFFFF" style="background:#FFFFFF">
<style>
    p {
        margin: 10px 0;
        font-size: 12pt;
        color: black;
        text-align: justify; 
        text-indent: 35.0pt;
    }
</style>

<p>Уважаемый клиент!</p>
<br />
<br />
<br />
<p>С 1 августа 2015 все права и обязанности по вашему Договору №<?=$contract?> передаются от ООО «МСН Телеком» к ООО «МСМ Телеком».</p>
<br /><br />
<p>Обращаем ваше внимание, что условия Договора остаются прежними.</p>
<p>Соглашение о передаче прав и обязанностей по договору №<?=$contract?> прилагается.</p><br />
<p>Абонентский ящик для отправки корреспонденции: 115162, г. Москва, а/я 46 для ООО «МСМ Телеком»</p>
<br />
<p>Если у вас есть вопросы, наши специалисты с удовольствием помогут вам:</p>
<p style="padding-left: 100px; text-indent: 0">
<u>info@mcn.ru</u><br />
<br />
Москва: +7 (495) 105-9999<br />
Санкт-Петербург: +7 (812) 372-6999<br />
Краснодар: +7 (861) 204-0099<br />
Екатеринбург: +7 (343) 302-0099<br />
Новосибирск: +7 (383) 312-0099<br />
Самара: +7 (846) 215-0099 <br />
Ростов-на-Дону: +7 (863) 309-0099<br />
Казань: +7 (843) 207-0099<br />
Нижний Новгород: +7 (831) 235-0099<br />
Владивосток: +7 (423) 206-0099<br />
</p>
</body>
</html>

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
 table.MsoNormalTable
	{mso-style-name:"Обычная таблица";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-unhide:no;
	mso-style-parent:"";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman","serif";}
    p {margin: 10px 0;}
</style>

<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 9.0pt; color: black;">
Уважаемый клиент!
</span></p>
<br />
<br />
<br />
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 9.0pt; color: black;">С 1 августа 2015 все права и обязанности по вашему Договору №<?=$contract?> передаются от ООО «МСН Телеком» к ООО «МСМ Телеком».
</span></p>
<br /><br />
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 9.0pt; color: black;">Обращаем ваше внимание, что условия Договора остаются прежними.
</span></p>
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 9.0pt; color: black;">Соглашение о передаче прав и обязанностей по договору №<?=$contract?> прилагается.
</span></p><br />
<br />
<br />
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 9.0pt; color: black;">Если у вас есть вопросы, наши специалисты с удовольствием помогут вам:<br />
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
</span></p>
</body>
</html>

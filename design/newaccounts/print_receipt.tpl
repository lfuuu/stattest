<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- link rel="stylesheet" href="style.css" type="text/css"-->
<title>Квитанция СБ РФ (ПД-4)</title>
<style>
/* CSS Document */

{literal}
body
{
    font-family:Arial, Helvetica, sans-serif;
    /*font-size:14px;*/
}

a
{
    color:#006400;
}

p
{
    padding: 5px 0px 0px 5px;
}

.vas ul
{
    padding: 0px 10px 0px 15px;
}

.vas li
{
    list-style-type:circle;
}

h3
{
    padding:0px 0px 0px 5px;
    font-size:100%; 
}

h1
{
    color:#006400;
    padding:0px 0px 0px 5px; 
    font-size:120%;
}

li
{
    list-style-type: none;
    padding-bottom:5px;
    padding: 6px 0px 0px 5px;
}

.main
{
    font-size:12px;
}

.list
{
    font-size:12px;
    padding: 6px 15px 0px 5px;
}

.main input
{
    font-size:12px;
    background-color:#CCFFCC;
}

.text14
{
    font-family:"Times New Roman", Times, serif;
    font-size:14px;
}
.text14 strong
{
    font-family:"Times New Roman", Times, serif;
    font-size:11px;
}

.link
{
    font-size:12px;
}
.link a
{
    text-decoration:none;
    color:#006400;
}

.link_u
{
    font-size:12px;
}
.link_u a
{
    color:#006400;
}
{/literal}
</style>

</head>

<body>
<div class="text14">
<table width="720" bordercolor="#000000" style="border:#000000 1px solid;" cellpadding="0" cellspacing="0">
<tr>
<td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">&nbsp;<strong>Извещение</strong></td>

{capture name=receipt}
<td valign="top" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">
<li><strong>Получатель: </strong><font style="font-size:90%"><u>ООО "МСН Телеком"</u></font>&nbsp;&nbsp;&nbsp;<br />  
<li><strong>КПП:</strong> <u>772401001</u>&nbsp;&nbsp;&nbsp;&nbsp; <strong>ИНН:</strong> <u>7727752084 </u>&nbsp;&nbsp;<font style="font-size:12px"> &nbsp;</font> 
&nbsp;     <li><strong>Код ОКАТО:</strong><u>45296571000</u>&nbsp;&nbsp;&nbsp;&nbsp;<strong>P/сч.:</strong> <u>40702810038110015462</u>&nbsp;&nbsp;
&nbsp;     <li> <strong>в:</strong> <font style="font-size:90%"><u> Московский банк Сбербанка России ОАО, г.Москва</u></font><br /> 
<li><strong>БИК:</strong> <u>044525225</u>&nbsp; <strong>К/сч.:</strong><u>30101810400000000225</u><br />
<li><strong>Код бюджетной классификации (КБК):</strong> ____________________ 
<li><strong>Платеж:</strong> <font style="font-size:90%"><u>Предоплата по лицевому счету &#8470;{$client.id} за телекоммуникационные услуги</u></font><br />
<li><strong>Плательщик:</strong>  {if $client.company}<u>{$client.company_full}</u>{else}_________________________________________________{/if}<br />
<li><strong>Адрес плательщика:</strong> <font style="font-size:90%"> {if $client.address_jur}<u>{$client.address_jur}</u>{else}____________________________________________{/if}</font><br />
<li><strong>ИНН плательщика:</strong> {if $client.inn}<u>{$client.inn}</u>{else}____________{/if}&nbsp;&nbsp;&nbsp;&nbsp; <strong>&#8470; л/сч. плательщика:</strong> ______________       <li><strong>Сумма:</strong> <b><u>{$sum.rub}</u></b> руб. <b><u>{$sum.kop}</u></b> коп. &nbsp;&nbsp;&nbsp;&nbsp;
            <strong>в том числе НДС:</strong>&nbsp;<u>{$sum.nds.rub}</u>&nbsp;руб.&nbsp;<u>{$sum.nds.kop}</u>&nbsp;коп.&nbsp;
&nbsp;<br /> 
&nbsp;<br /><br />
Подпись:________________________        &nbsp;&nbsp;Дата: &quot;{0|mdate:"d"}&quot;&nbsp;{0|mdate:"месяца Y г."}<br /><br /> 
</td>
{/capture}
{$smarty.capture.receipt}
</tr>
<tr>
<td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">&nbsp;<strong>Квитанция</strong></td>
{$smarty.capture.receipt}
</tr>
</table>
</div>
</body>
</html>


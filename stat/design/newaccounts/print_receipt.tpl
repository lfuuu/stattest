{capture name=receipt}
    <div><strong>Получатель: </strong><span style="font-size:90%" class="underline">{$organization.name}</span>&nbsp;&nbsp;&nbsp;</div>
    <div><strong>КПП:</strong> <span class="underline">{$organization.tax_registration_reason}</span>&nbsp;&nbsp;&nbsp;&nbsp; <strong>ИНН:</strong> <span class="underline">{$organization.tax_registration_id}</span>&nbsp;&nbsp;&nbsp;&nbsp;</div>
    <div><strong>ОКТМО:</strong>___________&nbsp;&nbsp;&nbsp;&nbsp;<strong>P/сч.:</strong> <span class="underline">{$organization.bank_account}</span>&nbsp;&nbsp;&nbsp;</div>
    <div><strong>в:</strong> <span style="font-size:90%" class="underline">{$organization.bank_name}</span></div>
    <div><strong>БИК:</strong> <span class="underline">{$organization.bank_bik}</span>&nbsp; <strong>К/сч.:</strong> <span class="underline">{$organization.bank_correspondent_account}</span></div>
    <div><strong>Код бюджетной классификации (КБК):</strong> ____________________     &nbsp;</div>
    <div><strong>Платеж:</strong> <span style="font-size:90%" class="underline">Предоплата по лицевому счету &#8470;{$client.id} за телекоммуникационные услуги</span></div>
    <div><strong>Плательщик:</strong> <span class="underline">{if $client.company}{$client.company_full}{else}_________________________________________________{/if}</span></div>
    <div><strong>Адрес плательщика:</strong> <span style="font-size:90%">{if $client.address_jur}<span class="underline">{$client.address_jur}</span>{else}____________________________________________{/if}</span></div>
    <div><strong>ИНН плательщика:</strong> {if $client.inn}<span class="underline">{$client.inn}</span>{else}____________{/if}&nbsp;&nbsp;&nbsp;&nbsp; <strong>№ л/сч. плательщика:</strong> ______________       </div>
    <div>
        <strong>Сумма:</strong>&nbsp;&nbsp;<span class="underline">{$sum.rub}</span> руб.&nbsp;<span class="underline">{$sum.kop}</span> коп.<br /><br /><br />
        Подпись: ________________________        Дата: &quot;{0|mdate:"d"}&quot;&nbsp;{0|mdate:"месяца Y г."}<br /><br />
    </div>
{/capture}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <title>Квитанция СБ РФ (ПД-4)</title>
    <style type="text/css">
    {literal}
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
        .text14 {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
        }
        .text14 div {
            list-style-type: none;
            padding: 6px 0 0 5px;
        }
        .text14 strong {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
        }
        .underline {
            text-decoration: underline;
        }
    {/literal}
    </style>
</head>

<body>
    <div class="text14">
        <table width="720" style="border:#000000 1px solid;" cellpadding="0" cellspacing="0">
            <tr>
                <td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">&nbsp;<strong>Извещение</strong><br /><br /><br /><img src="/utils/qr-code/receipt?data={$qrdata}"></td>
                <td valign="top" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">
                    {$smarty.capture.receipt}
                </td>
            </tr>
            <tr>
                <td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">&nbsp;<strong>Квитанция</strong></td>
                <td valign="top" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">
                    {$smarty.capture.receipt}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

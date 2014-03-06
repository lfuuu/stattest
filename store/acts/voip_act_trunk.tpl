<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<title>&#8470; 4-{$client.id}</title>
</head>
<body lang=RU style='tab-interval:35.4pt'>
<div class=Section1>
    <h2 align=center style='text-align:center'>блф &#8470; 4-{$client.id}</h2>
    <h3 align=center style='text-align:center'>УДБЮЙ-РТЙЕНЛЙ ТБВПФ</h3>
    <div align=center>
        <table border=0 cellpadding=0 width="96%">
            <tr>
                <td width="38%"><p>З. нПУЛЧБ </p></td>
                <td><p align=right>__________<span>љ </span>2014 З.</p></td>
            </tr>
        </table>
    </div>
    <p>оБУФПСЭЙК БЛФ УПУФБЧМЕО Ч ФПН, ЮФП {$firma.name}, Ч&nbsp;МЙГЕ
        {$firm_director.position_} {$firm_director.name_} Й пРЕТБФПТ {$client.company_full},
        Ч&nbsp;МЙГЕ {$client.signer_positionV} {$client.signer_nameV}, ЧЩРПМОЙМЙ ТБВПФЩ РП ПТЗБОЙЪБГЙЙ хУМХЗЙ
        РТЙУПЕДЙОЕОЙС Ч УППФЧЕФУФЧЙЙ У дПЗПЧПТПН &#8470;&nbsp;{$contract.contract_no}&nbsp;<span>&nbsp;ПФ&nbsp;{$contract.contract_date|mdate:'"d" НЕУСГБ Y'} З.</span>.</p>
    <p>тБВПФЩ РП ПТЗБОЙЪБГЙЙ хУМХЗЙ РТЙУПЕДЙОЕОЙС ХДПЧМЕФЧПТСАФ ХУМПЧЙСН дПЗПЧПТБ Й
        ЧЩРПМОЕОЩ У ОБДМЕЦБЭЙН ЛБЮЕУФЧПН. </p>
    <p>оБУФПСЭЙК бЛФ УПУФБЧМЕО Ч ДЧХИ ЬЛЪЕНРМСТБИ (РП ПДОПНХ ЬЛЪЕНРМСТХ ДМС ЛБЦДПК
        ЙЪ УФПТПО)<span style='mso-spacerun:yes'>љ </span>Й СЧМСЕФУС ПУОПЧБОЙЕН ДМС
        РТПЧЕДЕОЙС ТБУЮЕФПЧ НЕЦДХ {$firma.name} Й пРЕТБФПТПН. </p>
    <h5>йОЖПТНБГЙС ДМС РПМХЮЕОЙС УФБФЙУФЙЛЙ</h5>
    <p>уФТБОЙГБ РТПУНПФТБ: <i>https://lk.mcn.ru/</i><br>
{if $main_client}
        мПЗЙО: <span><b>{$main_client.id}</b></span><br />
        рБТПМШ: <b>{$main_client.password}</b>
{else}
        мПЗЙО: <span><b>{$client.id}</b></span><br />
        рБТПМШ: <b>{$client.password}</b>
{/if}
    </p>
    <p>
        <o:p>&nbsp;</o:p>
    </p>
    <p>
        <o:p>&nbsp;</o:p>
    </p>
    <table border=0 cellspacing=0 cellpadding=0 width="100%">
        <tr>
            <td><p>нуо фЕМЕЛПН: {$firma.name}</p></td>
            <td><p>пРЕТБФПТ: {$client.company_full}</p></td>
        </tr>
        <tr>
            <td><p><br>
                    {$firm_director.position} </p>
                <p>
                    <o:p>&nbsp;</o:p>
                </p>
                <p>___________ / {$firm_director.name} /</p></td>
            <td><p><br>
                    {$client.signer_position}</p>
                <p>
                    <o:p>&nbsp;</o:p>
                </p>
                <p>_____________/ {$client.signer_name} / </p></td>
        </tr>
    </table>
    <p>
        <o:p>&nbsp;</o:p>
    </p>
</div>
</body>
</html>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <TITLE>Díjbekérő &#8470;{$bill.bill_no}</TITLE>
    <META http-equiv=Content-Type content="text/html; charset=utf-8">
    <LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}bill.css" type="text/css" rel="stylesheet">
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >


<table width="100%">
    <tr><td>

            <b>Tel2tel Kft.</b><br />
            <b>Adószám:</b> 12773246-2-43 / HU12773246<br />
            <b>Bankszámla:</b><br />
            12010611- 01424475 - 00100006 Ft<br />
            12010611 - 01424475 - 00300000 Usd<br />
            12010611 - 01424475 - 00200003 Euro<br />
            Raiffeisen Bank Zrt. SWIFT UBRTHUHB<br />
            <b>Telefon:</b> +36 1 490-0999<br />
            <b>Fax:</b> +36 1 490-0998<br />
            <b>Postázási cím:</b> Budapest, 1114, Kemenes utca 8. félemelet 3. Magyarorsag<br />
            <b>Cégjegyzékszám:</b> 01 09 702746<br />
            <b>Cég email cím:</b> info@tel2tel.com
        </td>
        <td align=right>

            <table border="0" align="right">
                <div style="width: 110px;  text-align: center;padding-right: 10px;">

                        <img border="0" src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}tel2tel.png" width="115" />
                        tel2tel3.mcnhost.ru

                </div>

                <tr>
                    <td colspan=2 align="center">{if $bill_no_qr}<img src="{if $is_pdf == '1'}{$WEB_PATH}{else}./{/if}utils/qr-code/get?data={$bill_no_qr.bill}">{else}&nbsp;{/if}</td>
                </tr>
            </table>

        </td>
    </tr>
</table>
<hr>


<center><h2>Díjbekérő &#8470;{$bill.bill_no}</h2></center>



<p align=right>Dátum <b> {$bill.ts|mdate:"Y.m.d"} </b></p>

<hr>
<br>
<p><b>Vevő: Napsütéses Idő</b></p>

{assign var=isDiscount value=0}
{foreach from=$bill_lines item=line key=key}{assign var=key value=$key+1}
    {assign var=isDiscount value=`$isDiscount+$line.discount_auto+$line.discount_set`}
{/foreach}


<table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
    <tbody>
    <tr>
        <td align="center"><b>No</b></td>
        <td align="center"><b>Megnevezés</b></td>
        <td align="center"><b>Me</b></td>
        <td align="center"><b>Nettó egységár,&nbsp;Ft</b></td>
        <td align="center"><b>Nettó ár,&nbsp;Ft</b></td>
        <td align="center"><b>Áfa értéke, &nbsp;Ft</b></td>
        <td align="center"><b>Bruttó ár,&nbsp;Ft</b></td>
    </tr>

    {assign var=key value=0}


    {foreach from=$bill_lines item=line key=key}{assign var=key value=$key+1}

        {assign var=discount value=`$line.discount_auto+$line.discount_set`}
        <tr>
            <td align="right">{$key}</td>
            <td>{$line.item}</td>
            <td align="center">{$line.amount|mround:4:6}</td>
            <td align="center">{$line.outprice|round:4}</td>
            <td align="center">{$line.sum_without_tax|round:2}</td>
            <td align="center">{if $bill_client.nds_zero || $line.line_nds == 0}без НДС{else}{$line.sum_tax|round:2}{/if}</td>
            <td align="center">{$line.sum|round:2}</td>
        </tr>
    {/foreach}

    <tr>
        <td colspan="4">
            <p align="right"><b>Összesen:</b></p>
        </td>
        <td align="center">{$bill.sum_without_tax|round:2}</td>
        <td align="center">
            {if !$isDiscount}
                {if $bill_client.nds_zero}без НДС{else}{$bill.sum_tax|round:2}{/if}
            {else}
                &nbsp;
            {/if}
        </td>
        {if $isDiscount}
            <td align="center">&nbsp;</td>
            <td align="center">{$isDiscount|round:2}</td>
        {/if}
        <td align="center">{$bill.sum-$isDiscount|round:2}</td>
    </tr>

    </tbody></table>
<br>

<table border="0" align=center cellspacing="1" cellpadding="0"><tbody>
    <tr>
        <td>Vezérigazgatója</td>
        <td><br><br>_________________________________<br><br></td>
        <td>/ Melnikov A.K. /</td>
    </tr><tr>
        <td>Főkönyvelő</td>
        <td><br><br>_________________________________<br><br></td>
        <td>
            / Melnikov A.K. /
        </td>
    </tr>
    </tbody></table>

</body>
</html>

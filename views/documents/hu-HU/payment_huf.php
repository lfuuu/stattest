<?php

use app\classes\Utils;

/** @var $document app\classes\documents\DocumentReport */

$organization = $document->organization;

$payer_company = $document->getPayer();

$bill_date = Yii::$app->formatter->asDatetime($document->bill->bill_date, 'php:Y.m.d');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>Díjbekérő No <?= $document->bill->bill_no; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            body {margin-top: 0px;margin-left: 0px;}

            #page_1 {position:relative; overflow: hidden;margin: 19px 0px 23px 0px;padding: 0px;border: none;width: 793px;}
            #page_1 #id_1 {border:none;margin: 2px 0px 0px 304px;padding: 0px;border:none;width: 489px;overflow: hidden;}
            #page_1 #id_2 {border:none;margin: 5px 0px 0px 0px;padding: 0px;border:none;width: 793px;overflow: hidden;}
            #page_1 #id_2 #id_2_1 {float:left;border:none;margin: 0px 0px 0px 0px;padding: 0px;border:none;width: 408px;overflow: hidden;}
            #page_1 #id_2 #id_2_2 {float:left;border:none;margin: 35px 0px 0px 0px;padding: 0px;border:none;width: 385px;overflow: hidden;}
            #page_1 #id_3 {border:none;margin: 24px 0px 0px 19px;padding: 0px;border:none;width: 774px;overflow: hidden;}
            #page_1 #id_4 {border:none;margin: 47px 0px 0px 370px;padding: 0px;border:none;width: 423px;overflow: hidden;}

            .dclr {clear:both;float:none;height:1px;margin:0px;padding:0px;overflow:hidden;}

            .ft0{font: bold 16px 'Arial';line-height: 19px;}
            .ft1{font: 12px 'Arial';line-height: 15px;}
            .ft2{font: bold 13px 'Gabriola';line-height: 22px;}
            .ft3{font: 13px 'Arial';line-height: 16px;}
            .ft4{font: 11px 'Arial';line-height: 14px;}
            .ft5{font: bold 12pt 'Gabriola';line-height: 23px;}
            .ft6{font: bold 12pt 'Gabriola'; line-height: 15px;}
            .ft8{font: 1px 'Arial';line-height: 8px;}
            .ft9{font: 1px 'Arial';line-height: 2px;}
            .ft10{font: 1px 'Arial';line-height: 1px;}
            .ft11{font: 9px 'Arial';line-height: 12px;}
            .ft12{font: bold 20px 'Arial';line-height: 24px;}
            .ft13{font: bold 17px 'Arial';line-height: 19px;}
            .ft14{font: 1px 'Arial';line-height: 13px;}
            .ft15{font: bold 11px 'Arial';line-height: 14px;}
            .ft16{font: bold 12px 'Arial';line-height: 15px;}

            .p0{text-align: left;margin-top: 0px;margin-bottom: 0px;}
            .p1{text-align: right;padding-right: 12px;margin-top: 0px;margin-bottom: 0px;}
            .p2{text-align: left;padding-left: 30px;margin-top: 19px;margin-bottom: 0px;}
            .p3{text-align: left;padding-left: 38px;margin-top: 1px;margin-bottom: 0px;}
            .p4{text-align: left;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p5{text-align: left;padding-left: 46px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p6{text-align: left;padding-left: 8px;margin-top: 16px;margin-bottom: 0px;}
            .p7{text-align: left;padding-left: 8px;margin-top: 0px;margin-bottom: 0px;}
            .p8{text-align: left;padding-left: 46px;margin-top: 84px;margin-bottom: 0px;}
            .p9{text-align: left;padding-left: 38px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p10{text-align: left;padding-left: 30px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p11{text-align: left;padding-left: 2px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p12{text-align: left;padding-left: 1px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p13{text-align: left;padding-left: 19px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p14{text-align: center;padding-left: 104px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p15{text-align: center;padding-left: 15px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p16{text-align: left;padding-left: 56px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p17{text-align: left;padding-left: 98px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p22{text-align: left;padding-left: 54px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p23{text-align: left;padding-left: 320px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p24{text-align: center;padding-left: 298px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
            .p25{text-align: left;padding-left: 270px;margin-top: 66px;margin-bottom: 0px;}
            .p26{text-align: left;padding-left: 255px;margin-top: 8px;margin-bottom: 0px;}

            .td0{padding: 0px;margin: 0px;width: 116px;vertical-align: bottom;}
            .td1{padding: 0px;margin: 0px;width: 215px;vertical-align: bottom;}
            .td2{padding: 0px;margin: 0px;width: 140px;vertical-align: bottom;}
            .td3{padding: 0px;margin: 0px;width: 142px;vertical-align: bottom;}
            .td4{padding: 0px;margin: 0px;width: 123px;vertical-align: bottom;}
            .td5{padding: 0px;margin: 0px;width: 181px;vertical-align: bottom;}
            .td6{padding: 0px;margin: 0px;width: 170px;vertical-align: bottom;}
            .td7{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 140px;vertical-align: bottom;}
            .td12{padding: 0px;margin: 0px;width: 0px;vertical-align: bottom;}
            .td13{border-left: #000000 1px solid;border-right: #000000 1px solid;border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 189px;vertical-align: bottom;}
            .td14{padding: 0px;margin: 0px;width: 481px;vertical-align: bottom;}
            .td15{border-left: #000000 1px solid;border-right: #000000 1px solid;border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 189px;vertical-align: bottom;}

            .tr0{height: 17px;}
            .tr1{height: 19px;}
            .tr2{height: 23px;}
            .tr3{height: 8px;}
            .tr4{height: 2px;}
            .tr5{height: 29px;}
            .tr6{height: 16px;}
            .tr7{height: 36px;}
            .tr8{height: 26px;}
            .tr9{height: 15px;}
            .tr10{height: 20px;}
            .tr11{height: 32px;}
            .tr12{height: 42px;}
            .tr13{height: 33px;}
            .tr14{height: 9px;}
            .tr15{height: 24px;}
            .tr16{height: 13px;}

            .t0{width: 331px;margin-left: 38px;font: 12px 'Arial';}
            .t1{width: 756px;font: bold 9px 'Gabriola';}
            .t2{width: 672px;margin-left: 56px;margin-top: 408px;font: 12px 'Arial';}
        </style>
    </head>
    <body>
        <div id="page_1">
            <div class="dclr"></div>
            <div id="id_1">
                <P class="p0 ft0">Számviteli bizonylat</P>
            </div>
            <div id="id_2">
                <div id="id_2_1">
                    <p class="p1 ft1">1. oldal</p>
                    <p class="p2 ft2"><?= $organization->name; ?></p>
                    <p class="p3 ft3"><?= $organization->post_address; ?></p>
                    <table cellpadding="0" cellspacing="0" class="t0">
                        <tr>
                            <td class="tr0 td0"><P class="p4 ft1">Adószám</P></td>
                            <td class="tr0 td1"><P class="p5 ft1"><?= $organization->tax_registration_id; ?></P></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><P class="p4 ft1">Bank</P></td>
                            <td class="tr1 td1"><p class="p5 ft1"><?= $organization->bank_name; ?></p></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><p class="p4 ft1">Számlaszám</p></td>
                            <td class="tr1 td1"><P class="p5 ft4"><nobr><?= nl2br($organization->bank_account); ?></nobr></p></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><p class="p4 ft1">Telefon</p></td>
                            <td class="tr1 td1"><p class="p5 ft1"><?= $organization->contact_phone; ?></p></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><p class="p4 ft1">Fax</p></td>
                            <td class="tr1 td1"><p class="p5 ft1"><?= $organization->contact_fax; ?></p></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><P class="p4 ft1">Email</P></td>
                            <td class="tr1 td1"><p class="p5 ft1"><?= $organization->contact_email; ?></p></td>
                        </tr>
                        <tr>
                            <td class="tr1 td0"><p class="p4 ft1">Weboldal</p></td>
                            <td class="tr1 td1"><p class="p5 ft1"><?= $organization->contact_site; ?></p></td>
                        </tr>
                    </table>
                </div>
                <div id="id_2_2">
                    <p class="p0 ft5">Vevő</p>
                    <p class="p6 ft3"><?= ($payer_company['head_company'] ? $payer_company['head_company'] . ', ' : '') . $payer_company['company_full']; ?></p>
                    <p class="p7 ft3"><?= $payer_company['address_post']; ?></p>
                    <p class="p8 ft4">Ügyfélazonosító: <?= $payer_company['id']; ?></p>
                </div>
            </div>
            <div id="id_3">
                <table cellpadding="0" cellspacing="0" class="t1">
                    <tr>
                        <td class="tr0 td2"><p class="p9 ft6">Fizetés módja</p></td>
                        <td class="tr0 td3"><p class="p10 ft6">Kelt</p></td>
                        <td class="tr0 td4"><p class="p11 ft6">Teljesítés dátuma</p></td>
                        <td class="tr0 td5"><p class="p10 ft6">Fizetési határidő</p></td>
                        <td class="tr0 td6"><p class="p4 ft6">Sorszám</p></td>
                    </tr>
                    <tr>
                        <td class="tr2 td2"><p class="p9 ft1">Átutalás</p></td>
                        <td class="tr2 td3"><p class="p10 ft1"><?= $bill_date; ?></p></td>
                        <td class="tr2 td4"><p class="p12 ft1"><?= $bill_date; ?></p></td>
                        <td class="tr2 td5"><p class="p10 ft1"><?= $bill_date; ?></p></td>
                        <td class="tr2 td6"><P class="p4 ft1"><nobr><?= $document->bill->bill_no; ?></nobr></p></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="tr3 td7 p4 ft8">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="tr4 td7 p4 ft9">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tr5 td2 p13 ft6">Megnevezés</td>
                        <td class="tr5 td3 p14 ft6">M.e.</td>
                        <td class="tr5 td4 p15 ft6">Mennyiség</td>
                        <td class="tr5 td5 p16 ft6">Egységár</td>
                        <td class="tr5 td6 p17 ft6">Jóváírt összeg</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="tr6 td7 p4 ft10">&nbsp;</td>
                    </tr>
                    <?php foreach ($document->lines as $position => $line): ?>
                        <tr>
                            <td class="tr7 td2"><p class="ft1"><?= $line['item']; ?></p></td>
                            <td class="tr7 td3" align="center"><p class="ft1">alkalom</p></td>
                            <td class="tr7 td4" align="center"><p class="ft1"><?= Utils::mround($line['amount'], 4,6); ?></p></td>
                            <td class="tr7 td5"><p class="ft1"><?= Utils::round($line['price'], 4); ?></p></td>
                            <td class="tr7 td6"><p class="ft1"><?= Utils::round($line['sum'], 2); ?></p></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td class="tr8 td2"><p class="p13 ft11">(<?= $bill_date; ?> - <?= $bill_date; ?>)</p></td>
                        <td class="tr8 td3"><p class="p4 ft10">&nbsp;</p></td>
                        <td class="tr8 td4"><p class="p4 ft10">&nbsp;</p></td>
                        <td class="tr8 td5"><p class="p4 ft10">&nbsp;</p></td>
                        <td class="tr8 td6"><p class="p4 ft10">&nbsp;</p></td>
                    </tr>
                    <tr>
                        <td class="tr9 td2"><p class="p13 ft11">SZJ: 64.20.16</p></td>
                        <td colspan="4" class="tr9 td3 p4 ft10">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="tr10 td7 p4 ft10">&nbsp;</td>
                    </tr>
                </table>
                <table cellpadding=0 cellspacing=0 class="t2">
                    <tr>
                        <td class="tr11 td12"></td>
                        <td rowspan="2" class="tr12 td13"><p class="p22 ft12">FIZETVE</p></td>
                        <td class="tr13 td14"><p class="p23 ft1">Végösszeg: <?= Utils::round($document->sum, 2); ?> <?= $document->bill->currency; ?></p></td>
                    </tr>
                    <tr>
                        <td class="tr14 td12"></td>
                        <td rowspan=2 class="tr15 td14"><p class="p24 ft13">Jóváírva: <?= Utils::round($document->sum, 2); ?> <?= $document->bill->currency; ?></p></td>
                    </tr>
                    <tr>
                        <td class="tr16 td12"></td>
                        <td class="tr16 td15 p4 ft14">&nbsp;</td>
                    </tr>
                </table>
                <p class="p25 ft15">Aktuális egyenlegét a <?= $organization->name; ?> felületen láthatja.</p>
                <p class="p26 ft16">Köszönjük, hogy az <nobr><?= $organization->name; ?></nobr> választotta.</p>
            </div>
            <div id="id_4">
                <p class="p0 ft1">1. oldal</p>
            </div>
        </div>
    </body>
</html>

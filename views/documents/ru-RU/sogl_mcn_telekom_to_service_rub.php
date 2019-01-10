<?php

use app\classes\BillContract;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use app\models\Organization;
use app\models\Bill;

/** @var \app\models\ClientAccount $account */
$account = $document->bill->clientAccount;

$contractNumber = BillContract::getString($account->contract_id, time());

$contract = $account->contract;

if ($contract->state == \app\models\ClientContract::STATE_OFFER) {
    $contractNumber = 'б/н';
}

$contragent = $contract->contragent;

$organizationRetail = Organization::find()->byId(Organization::MCN_TELECOM)->actual()->one(); //Ретайл
$organizationService = Organization::find()->byId(Organization::MCN_TELECOM_SERVICE)->actual()->one(); //Сервис

$director_retail = $organizationRetail->director;
$director_service = $organizationService->director;
//$dateStr = \app\classes\DateFunction::mdate(strtotime(Bill::dao()->getNewCompanyDate($document->bill->client_id) ?: $document->bill->bill_date), '\&\l\a\q\u\o\;d\&\r\a\q\u\o\; месяца Y г.');
$dateStr = '&laquo;01&raquo; января 2019 г.';

$fsStyle = "";
if (isset($isPdf) && $isPdf) {
    $fSize = "9pt";
    $fsStyle = "font-size: " . $fSize . ";";
} else {
    $isPdf = false;
}

$isWithStamp = $isPdf;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <!-- title>Соглашение о передаче прав и обязанностей по договору: </title-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        <!--
        BODY {
            background: #FFFFFF;
        }

        P {
            margin: 10px 0;
            font-size: 11pt;
            color: black;
            text-align: justify;
            text-indent: 35.0pt;
        }

        -->
    </style>
</head>

<body>
<div class="Section1">
    <p style="<?= $fsStyle ?>text-align: center;" align="center">Соглашение о передаче прав и обязанностей</p>
    <p style="<?= $fsStyle ?>text-align: center;" align="center">по
        Договору между ООО «МСН Телеком» и <?= $contragent->name_full ?> №<?= $contractNumber ?></p>
    <table border="0" width="100%">
        <tr>
            <td style="text-align: justify;<?= $fsStyle ?>">
                г. Москва
            </td>
            <td style="text-align: right;<?= $fsStyle ?>">
                <?= $dateStr ?>&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
    </table>
    <p style="<?= $fsStyle ?>text-align: justify; text-indent: 35.0pt;">
        ООО «МСН Телеком» в лице Генерального директора Пыцкой Марины Алексеевны, действующей на основании Устава, с
        одной стороны, и ООО «МСН Телеком Сервис» в лице Генерального директора Кима Александра Геннадьевича,
        действующего на основании Устава, с другой стороны, при совместном упоминании именуемые Стороны, заключили
        настоящее Соглашение (далее - «Соглашение») о передаче прав и обязанностей по Договору между ООО «МСН Телеком»
        и <?= $contragent->name_full ?> №<?= $contractNumber ?> г. (далее - Договор) о нижеследующем:
    </p>

    <p style="<?= $fsStyle ?>text-align: justify;">1. ООО «МСН Телеком» с «01» января 2019 г. передает все свои права и
        обязанности по Договору, а ООО «МСН Телеком Сервис» принимает на себя с «01» января 2019 г. все передаваемые ООО
        «МСН Телеком» права и обязанности по Договору. Объем уступаемых прав по Договору определяется согласно сведениям
        о балансе, размещенным в личном кабинете на момент передачи прав.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">2. Стоимость уступки прав по Договору равна Объему уступаемых
        прав.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">3. ООО «МСН Телеком» извещает о том, что вся поступающая
        корреспонденция и платежи в рамках исполнения Договора с «01» января 2019 г. должны быть адресованы ООО «МСН
        Телеком Сервис».</p>
    <p style="<?= $fsStyle ?>text-align: justify;">4. Настоящее Соглашение вступает в законную силу с даты его
        подписания Сторонами.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">5. Передача прав и обязанностей в соответствии с настоящим
        Соглашением не влечет за собой каких-либо изменений условий Договора, кроме оговоренных в настоящем
        Соглашении.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">6. Настоящее Соглашение составлено на одном листе, в двух
        экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон, и является неотъемлемой частью
        Договора.</p>

    <p style="<?= $fsStyle ?>text-align: justify;"><strong>Реквизиты и подписи Сторон:</strong></p>
    <p style="<?= $fsStyle ?>margin-right: -2.0pt; text-align: justify;">
        <strong style="mso-bidi-font-weight: normal;">Общество с ограниченной ответственностью «МСН
            Телеком»</strong><br>
        Юридический адрес: 115487, г. Москва, 2-й Нагатинский проезд, д. 2, стр. 8, эт. 1, пом. II, ком. 7<br>
        ОГРН 1117746441647<br>
        ИНН: 7727752084;КПП:772401001<br>
        Банковские реквизиты:<br>
        р/с 40702810038110015462 в Московском Банке Сбербанка России ПАО г. Москва<br>
        к/с 30101810400000000225<br>
        БИК 044525225<br>
    </p>
    <p style="<?= $fsStyle ?>">

    <table width="90%">
        <tr style="height: 78px;<?= $fsStyle ?>">
            <td><br><br>Генеральный директор ООО «МСН Телеком»<br>М.А. Пыцкая<br><br><br></td>
            <td><?php if ($isWithStamp && MediaFileHelper::checkExists('SIGNATURE_DIR', $director_retail->signature_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        'align' => 'bottom',
                        'style' => 'position:relative; left:-50px; margin-top: -60px;',
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director_retail->signature_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director_retail->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>мп

                <?php if ($isWithStamp && MediaFileHelper::checkExists('STAMP_DIR', $organizationRetail->stamp_file_name)):
                    $image_options = [
                        'width' => 170,
                        'border' => 0,
                        //'style' => 'position:relative; top:10; left: -80px; margin-bottom:-170px; ',
                        //'style' => 'position:absolute; top:10; left: 80px; ',
                        'style' => 'float: left; margin: -2.5cm 0 0 0.5cm;'
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organizationRetail->stamp_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organizationRetail->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?></td>
        </tr>
    </table>

    </p>
    <p style="<?= $fsStyle ?>text-align: justify;">
        <strong style="mso-bidi-font-weight: normal;">Общество с ограниченной ответственностью «МСН Телеком
            Сервис»</strong><br>
        Юридический адрес: 117574, г. Москва, Одоевского проезд, дом 3, корп.7, этаж 1, помещение II, офис 41<br>
        ОГРН: 1187746815761<br>
        ИНН: 7728445664; КПП: 772801001;<br>
        Почтовый адрес: 115487, г. Москва, 2-й Нагатинский проезд, д.2, стр.8<br>
        Банковские реквизиты:<br>
        р/с 40702810338000213883 в Московском Банке Сбербанка России ПАО г. Москва<br>
        к/с 30101810400000000225<br>
        БИК 044525225
    </p>
    <p style="<?= $fsStyle ?>">
    <table width="90%">
        <tr style="<?= $fsStyle ?>">
            <td><br><br>
                Генеральный директор ООО «МСН Телеком Сервис»
                <br>А. Г. Ким<br><br><br></td>
            <td><?php if ($isWithStamp && MediaFileHelper::checkExists('SIGNATURE_DIR', $director_service->signature_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        'style' => 'position:relative; margin-top: -30px; left: -30px; vertical-align: middle'
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director_service->signature_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director_service->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?></td>
            <td>мп

                <?php if ($isWithStamp && MediaFileHelper::checkExists('STAMP_DIR', $organizationService->stamp_file_name)):
                    $image_options = [
                        'width' => 170,
                        'border' => 0,
                        //'style' => 'position:absolute; margin-top: -90px; left: 480px; vertical-align: middle',
                        'style' => 'float: left; margin: -1.5cm 0 0 0.5cm;'
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organizationService->stamp_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organizationService->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?></td>
        </tr>
    </table>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;


    </p>
</div>
</body>
</html>

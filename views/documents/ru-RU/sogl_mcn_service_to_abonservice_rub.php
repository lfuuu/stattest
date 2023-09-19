<?php

use app\classes\BillContract;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use app\models\ClientContractAdditionalAgreement;use app\models\Organization;
use app\models\Bill;use app\models\OrganizationSettlementAccount;

/** @var \app\models\ClientAccount $account */
$account = $document->bill->clientAccount;

$contractNumber = BillContract::getString($account->contract_id, time());

$contract = $account->contract;

if ($contract->state == \app\models\ClientContract::STATE_OFFER) {
    $contractNumber = 'б/н';
}

$contragent = $contract->contragent;

/** @var ClientContractAdditionalAgreement $info */
$info = ClientContractAdditionalAgreement::find()->where([
    'account_id' => $account->id,
    'from_organization_id' => Organization::MCN_TELECOM_SERVICE,
    'to_organization_id' => Organization::AB_SERVICE_MARCOMNET,
    // 'transfer_date' => $document->bill->bill_date,
])->one();

if (!$info) {
    return;
}

$documentDateTs = (new DateTimeImmutable($info->transfer_date))->modify('-11 day')->getTimestamp();

$documentDateStr =
    "&laquo;" .
    \app\classes\DateFunction::mdate($documentDateTs, 'd') .
    "&raquo; " .
    \app\classes\DateFunction::mdate($documentDateTs, 'месяца Y г.');


$organizationService = Organization::find()->byId(Organization::MCN_TELECOM_SERVICE)->actual()->one(); //Сервис
$organizationAbonService = Organization::find()->byId(Organization::AB_SERVICE_MARCOMNET)->actual()->one(); //АбонСервисе

$director_service = $organizationService->director;
$director_abonservice = $organizationAbonService->director;
//$dateStr = \app\classes\DateFunction::mdate(strtotime(Bill::dao()->getNewCompanyDate($document->bill->client_id) ?: $document->bill->bill_date), '\&\l\a\q\u\o\;d\&\r\a\q\u\o\; месяца Y г.');
//$dateStr = '"&laquo;"01&raquo; сентября 2023 г.';
$dateTime = strtotime($info['transfer_date']);
$dateStr =
    "&laquo;" .
    \app\classes\DateFunction::mdate($dateTime, 'd') .
    "&raquo; " .
    \app\classes\DateFunction::mdate($dateTime, 'месяца Y г.');

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
        Договору между <?= $organizationService->name ?> и <?= $contragent->name_full ?> №<?= $contractNumber ?></p>
    <table border="0" width="100%">
        <tr>
            <td style="text-align: justify;<?= $fsStyle ?>">
                г. Москва
            </td>
            <td style="text-align: right;<?= $fsStyle ?>">
                <?= $documentDateStr ?>
            </td>
        </tr>
    </table>
    <p style="<?= $fsStyle ?>text-align: justify; text-indent: 35.0pt;">
        <?= $organizationService->name ?> в лице Генерального директора Кима А.Г.,
        действующего на основании Устава, с
        одной стороны, и <?= $organizationAbonService->name ?> в лице Генерального
        директора Бирюковой Н. В.,
        действующей на основании Устава, с другой стороны, при совместном упоминании именуемые Стороны, заключили
        настоящее Соглашение (далее - «Соглашение») о передаче прав и обязанностей по Договору
        между <?= $organizationService->name ?>
        и <?= $contragent->name_full ?> №<?= $contractNumber ?> г. (далее - Договор) о нижеследующем:
    </p>

    <p style="<?= $fsStyle ?>text-align: justify;">1. <?= $organizationService->name ?> с <?= $dateStr ?> передает все
        свои права и
        обязанности по Договору, а <?= $organizationAbonService->name ?> принимает на себя с <?= $dateStr ?> все
        передаваемые
        <?= $organizationService->name ?> права и обязанности по Договору. Объем уступаемых прав по Договору
        определяется согласно сведениям
        о балансе, размещенным в личном кабинете на момент передачи прав.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">2. Стоимость уступки прав по Договору равна Объему уступаемых
        прав.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">3. <?= $organizationService->name ?> извещает о том, что вся
        поступающая
        корреспонденция и платежи в рамках исполнения Договора с <?= $dateStr ?> должны быть адресованы
        <?= $organizationAbonService->name ?>.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">4. Настоящее Соглашение вступает в законную силу с даты его
        подписания Сторонами.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">5. Передача прав и обязанностей в соответствии с настоящим
        Соглашением не влечет за собой каких-либо изменений условий Договора, кроме оговоренных в настоящем
        Соглашении.</p>
    <p style="<?= $fsStyle ?>text-align: justify;">6. Настоящее Соглашение составлено на одном листе, в двух
        экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон, и является неотъемлемой частью
        Договора.</p>

    <p style="<?= $fsStyle ?>text-align: justify;"><strong>Реквизиты и подписи Сторон:</strong></p>
    <p style="<?= $fsStyle ?>text-align: justify;">
        <strong style="mso-bidi-font-weight: normal;"><?= $organizationService->full_name ?></strong><br>
        Юридический адрес: <?= $organizationService->legal_address ?><br>
        ОГРН: <?= $organizationService->registration_id ?><br>
        ИНН: <?= $organizationService->tax_registration_id ?>; КПП: <?= $organizationService->tax_registration_reason ?>
        ;<br>
        Почтовый адрес: <?= $organizationService->post_address ?><br>
        Банковские реквизиты:<br>
        <?php
        $settlementAccount = $organizationService->getSettlementAccount(OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA);
        $bankAccount = $settlementAccount->getAttributes();
        ?>
        р/с <?= $settlementAccount->getProperty('bank_account_RUB') ?> в <?= $bankAccount['bank_name'] ?><br>
        к/с <?= $bankAccount['bank_correspondent_account'] ?><br>
        БИК <?= $bankAccount['bank_bik'] ?>
    </p>
    <p style="<?= $fsStyle ?>">

    <table width="90%">
        <tr style="height: 78px;<?= $fsStyle ?>">
            <td><br><br>Генеральный директор <?= $organizationService->name ?><br><?= $organizationService->director ?>
                <br><br><br></td>
            <td><?php if ($isWithStamp && MediaFileHelper::checkExists('SIGNATURE_DIR', $director_service->signature_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        'align' => 'bottom',
                        'style' => 'position:relative; left:-50px; margin-top: -60px;',
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
                <?php endif; ?>
            </td>
            <td>мп

                <?php if ($isWithStamp && MediaFileHelper::checkExists('STAMP_DIR', $organizationService->stamp_file_name)):
                    $image_options = [
                        'width' => 170,
                        'border' => 0,
                        //'style' => 'position:relative; top:10; left: -80px; margin-bottom:-170px; ',
                        //'style' => 'position:absolute; top:10; left: 80px; ',
                        'style' => 'float: left; margin: -2.5cm 0 0 0.5cm;'
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

    </p>
    <p style="<?= $fsStyle ?>text-align: justify;">
        <strong style="mso-bidi-font-weight: normal;"><?= $organizationAbonService->full_name ?></strong><br>
        Юридический адрес: <?= $organizationAbonService->legal_address ?><br>
        ОГРН: <?= $organizationAbonService->registration_id ?><br>
        ИНН: <?= $organizationAbonService->tax_registration_id ?>;
        КПП: <?= $organizationAbonService->tax_registration_reason ?>;<br>
        Почтовый адрес: <?= $organizationAbonService->post_address ?><br>
        Банковские реквизиты:<br>
        <?php
        $settlementAccount = $organizationAbonService->getSettlementAccount(OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA);
        $bankAccount = $settlementAccount->getAttributes();
        ?>
        р/с <?= $settlementAccount->getProperty('bank_account_RUB') ?> в <?= $bankAccount['bank_name'] ?><br>
        к/с <?= $bankAccount['bank_correspondent_account'] ?><br>
        БИК <?= $bankAccount['bank_bik'] ?>
    </p>
    <p style="<?= $fsStyle ?>">
    <table width="90%">
        <tr style="<?= $fsStyle ?>">
            <td><br><br>
                Генеральный директор <?= $organizationAbonService->name ?>
                <br><?= $organizationAbonService->director ?><br><br><br></td>
            <td><?php if ($isWithStamp && MediaFileHelper::checkExists('SIGNATURE_DIR', $director_abonservice->signature_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        'style' => 'position:relative; margin-top: -30px; left: -30px; vertical-align: middle'
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director_abonservice->signature_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director_abonservice->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?></td>
            <td>мп

                <?php if ($isWithStamp && MediaFileHelper::checkExists('STAMP_DIR', $organizationAbonService->stamp_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        //'style' => 'position:absolute; margin-top: -90px; left: 480px; vertical-align: middle',
                        'style' => 'float: left; margin: -1.5cm 0 0 0.5cm;'
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organizationAbonService->stamp_file_name), $image_options);
                    else:
                        array_walk($image_options, function (&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organizationAbonService->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?></td>
        </tr>
    </table>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;


    </p>
</div>
</body>
</html>

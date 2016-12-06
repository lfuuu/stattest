<?php

use app\classes\BillContract;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use app\models\Organization;

$contract = BillContract::getString($document->bill->clientAccount->contract_id, time());

$organizationMCNTelekom = Organization::find()->byId(1)->actual()->one(); //mcn_telekom
$organizationMCMTelekom = Organization::find()->byId(11)->actual()->one(); //mcm_telekom

$director_mcn = $organizationMCNTelekom->director;
$director_mcm = $organizationMCMTelekom->director;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Соглашение о передаче прав и обязанностей по договору: </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
        body {
            background: #FFFFFF;
        }
        p {
            margin: 10px 0;
            font-size: 11pt;
            color: black;
            text-align: justify;
            text-indent: 35.0pt;
        }
        </style>
    </head>

    <body>
        <div class="Section1">
            <p style="text-align: center;" align="center">Соглашение о передаче прав и обязанностей по</p>
            <p style="text-align: center;" align="center">Договору № <?= $contract ?></p>
            <table border="0" width="100%">
                <tr>
                    <td style="text-align: justify;">
                        г. Москва
                    </td>
                    <td style="text-align: right;">
                        &laquo;01&raquo; апреля 2016 г.&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
            </table>
            <p style="text-align: justify; text-indent: 35.0pt;">ООО &laquo;МСН Телеком&raquo; в лице Генерального директора Пыцкой Марины Алексеевны, действующей на основании Устава, с одной стороны,</p>
            <p style="text-align: justify; text-indent: 35.0pt;">ООО &laquo;МСМ Телеком&raquo; в лице Генерального директора Бирюковой Натальи Викторовны, действующей на основании Устава, с другой стороны,</p>
            <p style="text-align: justify; text-indent: 35.0pt;">при совместном упоминании именуемые Стороны, а по отдельности Сторона, заключили настоящее Соглашение (далее - &laquo;Соглашение&raquo;) о передаче прав и обязанностей по Договору № <?= $contract?> (далее - Договор) о нижеследующем:</p>
            <p style="text-align: justify;">1. ООО &laquo;МСН Телеком&raquo; с &laquo;01&raquo; апреля 2016 г. передает все свои права и обязанности по Договору, а ООО &laquo;МСМ Телеком&raquo; принимает на себя с &laquo;01&raquo; апреля 2016 г. все передаваемые ООО &laquo;МСН Телеком&raquo; права и обязанности по Договору.</p>
            <p style="text-align: justify;">2. С &laquo;01&raquo; апреля 2016 г. права и обязанности по Договору возникают у ООО &laquo;МСМ Телеком&raquo;, а обязанности в отношении ООО &laquo;МСН Телеком&raquo; прекращаются.</p>
            <p style="text-align: justify;">3. ООО &laquo;МСН Телеком&raquo; передает ООО &laquo;МСМ Телеком&raquo; свой оригинальный экземпляр Договора.</p>
            <p style="text-align: justify;">4. ООО &laquo;МСМ Телеком&raquo; извещает о том, что вся поступающая корреспонденция в рамках исполнения Договора с &laquo;01&raquo; апреля 2016 г. должна быть адресована в ООО &laquo;МСМ Телеком&raquo;.</p>
            <p style="text-align: justify;">5. Настоящее Соглашение вступает в законную силу с даты его подписания Сторонами.</p>
            <p style="text-align: justify;">6. Передача прав и обязанностей в соответствии с настоящим Соглашением не влечет за собой каких-либо изменений условий Договора, кроме оговоренных в настоящем Соглашении.</p>
            <p style="text-align: justify;">7. Настоящее Соглашение составлено на одном листе, в двух экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон, и является неотъемлемой частью Договора.</p>

            <p style="text-align: justify;"><strong>Место нахождения и банковские реквизиты Сторон:</strong></p>
            <p style="margin-right: -2.0pt; text-align: justify; font-size: 10pt;">
                <strong style="mso-bidi-font-weight: normal;">Общество с ограниченной ответственностью&nbsp; &laquo;МСН Телеком&raquo;</strong><br>
                    Юридический адрес: 123098, г. Москва, ул. Академика Бочвара, д. 10Б<br>
                    ОГРН 1117746441647 ИНН 7727752084 &nbsp;КПП 773401001<br>
                    р/с 40702810038110015462 в Московском Банке Сбербанка России ОАО г. Москва<br>
                    к/с 30101810400000000225, БИК 044525225
            </p>
            <p>
                М.А. Пыцкая
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <?php if(MediaFileHelper::checkExists('SIGNATURE_DIR', $director_mcn->signature_file_name)):
                    $image_options = [
                        'width' => 140,
                        'border' => 0,
                        'align' => 'bottom',
                        'style' => 'position:relative; left:-50px',
                    ];

                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director_mcn->signature_file_name), $image_options);
                    else:
                        array_walk($image_options, function(&$item, $key) {
                            $item = $key . '="' . $item . '"';
                        });
                        ?>
                        <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director_mcn->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
                    <?php endif; ?>
                <?php endif; ?>

                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                    мп

                    <?php if (MediaFileHelper::checkExists('STAMP_DIR', $organizationMCNTelekom->stamp_file_name)):
                        $image_options = [
                            'width' => 200,
                            'border' => 0,
                            'style' => 'position:relative; top:-140; z-index:-10; margin-bottom:-170px;',
                        ];

                        if ($inline_img):
                            echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organizationMCNTelekom->stamp_file_name), $image_options);
                        else:
                            array_walk($image_options, function(&$item, $key) {
                                $item = $key . '="' . $item . '"';
                            });
                            ?>
                            <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organizationMCNTelekom->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                        <?php endif; ?>
                    <?php endif; ?>

                </p>
                <p style="text-align: justify; font-size: 10pt;">
                    <strong style="mso-bidi-font-weight: normal;">Общество с ограниченной ответственностью &laquo;МСМ Телеком&raquo;</strong><br>
                    Юридический адрес: 117574, г. Москва, Одоевского проезд, д. 3, корп. 7<br>
                    ОГРН 1157746324834 &nbsp;ИНН 7728226648 &nbsp;КПП 772801001<br>
                    р
                    /с 40702810038000034045 в Московском Банке Сбербанка России ОАО г. Москва<br>
                    к/с 3010181040000000225, БИК 044525225
                </p>
                <p>
                    Н.В. Бирюкова
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                    <?php if(MediaFileHelper::checkExists('SIGNATURE_DIR', $director_mcm->signature_file_name)):
                        $image_options = [
                            'width' => 140,
                            'border' => 0,
                            'style' => 'position:relative; margin-top: -100px; left: -30px; vertical-align: middle'
                        ];

                        if ($inline_img):
                            echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director_mcm->signature_file_name), $image_options);
                        else:
                            array_walk($image_options, function(&$item, $key) {
                                $item = $key . '="' . $item . '"';
                            });
                            ?>
                            <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director_mcm->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
                        <?php endif; ?>
                    <?php endif; ?>

                    мп

                    <?php if (MediaFileHelper::checkExists('STAMP_DIR', $organizationMCMTelekom->stamp_file_name)):
                        $image_options = [
                            'width' => 170,
                            'border' => 0,
                            'style' => 'position:absolute; margin-top: -90px; left: 480px; vertical-align: middle',
                        ];

                        if ($inline_img):
                            echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organizationMCMTelekom->stamp_file_name), $image_options);
                        else:
                            array_walk($image_options, function(&$item, $key) {
                                $item = $key . '="' . $item . '"';
                            });
                            ?>
                            <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organizationMCMTelekom->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>
    </body>
</html>

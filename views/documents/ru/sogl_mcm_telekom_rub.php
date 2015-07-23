<?php

use app\classes\BillContract;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use app\models\Organization;

$contract = BillContract::getString($document->bill->clientAccount->id, time());

$organizationMCNTelekom = Organization::find()->byId(1)->actual()->one(); //mcn_telekom
$organizationMCMTelekom = Organization::find()->byId(11)->actual()->one(); //mcm_telekom

$director_mcn = $organizationMCNTelekom->director;
$director_mcm = $organizationMCMTelekom->director;


?><?= $director->post_nominative; ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Соглашение о передаче прав и обязанностей по договору: </title>
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
</style>
<div class="Section1">
<p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 11.0pt; color: black;">Соглашение о передаче прав и обязанностей <span class="GramE">по</span></span></p>
<p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 11.0pt; color: black;">Договору № <?= $contract ?></p>
<table border=0 width=100%>
    <tr>
        <td>
            <p class="MsoNormal" style="text-align: justify;">
                <span style="font-size: 11.0pt; color: black;">г. Москва</span>
            </p>
        </td>
        <td>
            <p class="MsoNormal" style="text-align: right;">
                <span style="font-size: 11.0pt; color: black;">&laquo;01&raquo; августа 2015 г.&nbsp;&nbsp;&nbsp;</span>
            </p>
        </td>
    </tr>
</table>
<p class="MsoNormal" style="text-align: justify;"><strong><span style="font-size: 11.0pt; color: black;"><span style="mso-spacerun: yes;">&nbsp;</span></span></strong></p>
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 11.0pt; color: black;">ООО &laquo;МСН Телеком&raquo; в лице Генерального директора <span class="SpellE">Пыцкой</span> Марины Алексеевны, действующей на основании Устава, с одной стороны,</span></p>
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 11.0pt; color: black;">ООО &laquo;МСМ Телеком&raquo; в лице Генерального директора Бирюковой Натальи Викторовны, действующей на основании Устава, с другой стороны,</span></p>
<p class="MsoNormal" style="text-align: justify; text-indent: 35.0pt;"><span style="font-size: 11.0pt; color: black;">при совместном упоминании именуемые Стороны, а по отдельности Сторона, заключили настоящее Соглашение (далее - &laquo;Соглашение&raquo;) о передаче прав и обязанностей по Договору № <?= $contract?> (далее - Договор) о нижеследующем:</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">1. ООО &laquo;МСН Телеком&raquo; с &laquo;01&raquo; августа 2015 г. передает все свои права и обязанности <span class="GramE">по</span> Договору, а ООО &laquo;МСМ Телеком&raquo; принимает на себя с &laquo;01&raquo; августа 2015 г. все передаваемые ООО &laquo;МСН Телеком&raquo; права и обязанности по Договору.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">2. С &laquo;01&raquo; августа 2015 г. права и обязанности по Договору возникают <span class="GramE">у ООО</span> &laquo;МСМ Телеком&raquo;, а обязанности в отношении ООО &laquo;МСН Телеком&raquo; прекращаются.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">3. ООО &laquo;МСН Телеком&raquo; передает ООО &laquo;МСМ Телеком&raquo; свой оригинальный экземпляр Договора.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">4. ООО &laquo;МСМ Телеком&raquo; извещает о том, что вся поступающая корреспонденция в рамках исполнения Договора с &laquo;01&raquo; августа 2015 г. должна быть адресована в ООО &laquo;МСМ Телеком&raquo;.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">5. Настоящее Соглашение вступает в законную силу <span class="GramE">с даты</span> его подписания Сторонами.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">6. Передача прав и обязанностей в соответствии с настоящим Соглашением не влечет за собой каких-либо изменений условий Договора, <span class="GramE">кроме</span> оговоренных в настоящем Соглашении.</span></p>
<p class="MsoNormal" style="text-align: justify;"><span style="font-size: 11.0pt; color: black;">7. Настоящее Соглашение составлено на одном листе, в двух экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон, и является неотъемлемой частью Договора.</span></p>
<p class="MsoNormal" style="text-align: justify;"><strong><span style="font-size: 11.0pt; color: black;"><span style="mso-spacerun: yes;">&nbsp;</span></span></strong></p>
<p class="MsoNormal" style="text-align: justify;"><strong><span style="font-size: 11.0pt; color: black;">Место нахождения и банковские реквизиты Сторон:</span></strong></p>
<p class="MsoNormal" style="margin-right: -2.0pt; text-align: justify;"><strong style="mso-bidi-font-weight: normal;"><span style="font-size: 11.0pt; color: black;">Общество с ограниченной ответственностью<span style="mso-spacerun: yes;">&nbsp; </span>&laquo;МСН Телеком&raquo;</span></strong><br>
<span style="font-size: 11.0pt; color: black;">Юридический адрес: </span>123098, г. Москва, ул. Академика <span class="SpellE">Бочвара</span>, д. 10Б<br>
ОГРН 1117746441647 <span style="font-size: 11.0pt; color: black;">ИНН </span>7727752084 <span style="font-size: 11.0pt; color: black;"><span style="mso-spacerun: yes;">&nbsp;</span>КПП </span>773401001<br>
р/с 40702810038110015462 в Московском Банке Сбербанка России ОАО г. Москва<br>
к/с 30101810400000000225, БИК 044525225</p>

<p class="MsoNormal"><span style="font-size: 11.0pt; color: black;">М.А. <span class="SpellE">Пыцкая</span><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                        <?php if(MediaFileHelper::checkExists('SIGNATURE_DIR', $director_mcn->signature_file_name)):
                            $image_options = [
                                'width' => 140,
                                'border' => 0,
                                'align' => 'bottom',
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
                                    <span style="font-size: 11.0pt; color: black;"><span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><span style="mso-tab-count: 1;">&nbsp;&nbsp;&nbsp;&nbsp; </span><span class="SpellE">мп




                        <?php if (MediaFileHelper::checkExists('STAMP_DIR', $organizationMCNTelekom->stamp_file_name)):
                            $image_options = [
                                'width' => 200,
                                'border' => 0,
                                'style' => 'position:relative; left:-80; top:-100; z-index:-10; margin-bottom:-170px;',
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




</span></span></p>
<p class="MsoNormal" style="text-align: justify;"><strong style="mso-bidi-font-weight: normal;"><span style="font-size: 11.0pt; color: black;">Общество с ограниченной ответственностью &laquo;МСМ Телеком&raquo;</span></strong><br>
<span style="font-size: 11.0pt; color: black;">Юридический адрес: </span>117574, г. Москва, Одоевского проезд, д. 3, корп. 7<br>
ОГРН 1157746324834 <span style="mso-spacerun: yes;">&nbsp;</span><span style="font-size: 11.0pt; color: black;">ИНН </span>7728226648 <span style="font-size: 11.0pt; color: black;"><span style="mso-spacerun: yes;">&nbsp;</span>КПП </span>772801001<br>
<span class="GramE"><span style="font-size: 11.0pt; color: black;">р</span></span><span style="font-size: 11.0pt; color: black;">/с </span>40702810038000034045<span style="font-size: 11.0pt; color: black;"> в Московском Банке Сбербанка России ОАО г. Москва</span><br>
<span style="font-size: 11.0pt; color: black;">к/с </span>3010181040000000225<span style="font-size: 11.0pt; color: black;">, БИК </span>044525225</p>

<p class="MsoNormal"><span style="font-size: 11.0pt; color: black;">Н.В. Бирюкова<span style="mso-spacerun: yes;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                        <?php if(MediaFileHelper::checkExists('SIGNATURE_DIR', $director_mcm->signature_file_name)):
                            $image_options = [
                                'width' => 140,
                                'border' => 0,
                                'style' => 'position:relative; margin-left: -100px; margin-top: -15px; vertical-align: middle'
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

<?php if (MediaFileHelper::checkExists('STAMP_DIR', $organizationMCMTelekom->stamp_file_name)):
                            $image_options = [
                                'width' => 170,
                                'border' => 0,
                                'style' => 'position:relative; left:-40; top:-60; z-index:-10; margin-bottom:-170px;',
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
                                <br>

                                    <span style="font-size: 11.0pt; color: black; padding-left: 500px;"><span style="mso-tab-count: 1;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><span class="SpellE">мп
</span>



</span></p>
</div>
</body>
</html>

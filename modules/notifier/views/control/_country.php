<?php

use kartik\widgets\ActiveForm;

/**
 * @var \app\modules\notifier\forms\ControlForm $dataForm
 * @var int $countryCode
 */

$form = ActiveForm::begin();
echo $form
    ->field($dataForm, 'country_code')
    ->hiddenInput([
        'value' => $countryCode
    ])
    ->label(false);

$schemePublishData = $dataForm->getSchemeLastPublishData($countryCode);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-10">
            <b>Кол-во клиентов на схеме</b>: <?= $dataForm->getCountOfClientsInCountry($countryCode) ?><br />
            <b>Обновлено: </b> <?= ($schemePublishData !== null ? (string)$schemePublishData : 'Данные отсутствуют') ?>
        </div>
        <div class="col-sm-2">
            <?= $this->render('//layouts/_submitButton',
                [
                    'text' => 'Опубликовать схему',
                    'params' => [
                        'class' => 'btn btn-primary',
                        'style' => $style,
                    ],
                ]
            ) ?>
        </div>
    </div>
</div>

<?php
ActiveForm::end();
<?php

use kartik\widgets\ActiveForm;

/**
 * @var \app\modules\notifier\forms\ControlForm $dataForm
 * @var int $countryCode
 */

$form = ActiveForm::begin([
    'action' => ['/notifier/control/apply-scheme', 'countryCode' => $countryCode,],
]);

$scheme = $dataForm->getScheme($countryCode);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-10">
            <?php if (!count($scheme->log)) : ?>
                <b>Данные о применении схемы отсутствуют</b>
            <?php else:
                $firstRecord = reset($scheme->prettyLog);
                ?>
                <b>Обновлено: </b> <?= $firstRecord->userName ?> (<?= $firstRecord->updated ?>)<br />
                <div class="label label-danger"><?= $firstRecord->message ?></div>
            <?php endif; ?>
        </div>
        <div class="col-sm-2">
            <?= $this->render('//layouts/_submitButton', [
                'text' => 'Опубликовать схему',
                'params' => [
                    'class' => 'btn btn-primary',
                    'style' => $style,
                ],
            ]) ?>

        </div>
    </div>
</div>

<?php
ActiveForm::end();
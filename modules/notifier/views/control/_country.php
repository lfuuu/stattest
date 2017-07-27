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
                    <b>Обновлено: </b> <?= $firstRecord->userName ?> (<?= $firstRecord->updated ?>)<br/>
                    <div class="label label-danger"><?= $firstRecord->message ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-2 text-right">
                <?php if ($scheme->isActual($countryCode)) : ?>
                    <div class="label label-success">
                        Обновление не требуется
                    </div>
                <?php else : ?>
                    <?= $this->render('//layouts/_submitButton', [
                        'text' => 'Опубликовать схему',
                        'params' => [
                            'class' => 'btn btn-primary',
                        ],
                    ]) ?>
                <?php endif ?>
            </div>
        </div>
    </div>

<?php
ActiveForm::end();
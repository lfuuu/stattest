<?php

use app\helpers\DateTimeZoneHelper;
use kartik\widgets\ActiveForm;

/**
 * @var \app\modules\notifier\forms\ControlForm $dataForm
 * @var int $countryCode
 */

$form = ActiveForm::begin([
    'action' => ['/notifier/control/apply-scheme', 'countryCode' => $countryCode,],
]);

$schemePublishData = $dataForm->getSchemeLastPublishData($countryCode);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-10">
            <b>Кол-во клиентов на схеме</b>: <?= $dataForm->getCountOfClientsInCountry($countryCode) ?><br />
            <?php if ($schemePublishData !== null) :?>
                <b>Обновлено: </b> <?= (string)$schemePublishData ?><br />
                <?php
                if (!is_null($schemePublishData->updated_at)) :
                    $updatedAt = (new \DateTime($schemePublishData->updated_at, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
                        ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW))
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                    ?>
                    <b><?= $updatedAt ?></b>:
                    <?= $schemePublishData->result ?> записей обновлено
                <?php endif; ?>
            <?php else :?>
                <b>Данные о применении схемы отсутствуют</b>
            <?php endif; ?>
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
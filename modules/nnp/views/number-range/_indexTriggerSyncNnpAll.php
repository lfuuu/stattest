<?php
/**
 * Диапазон номеров. Триггер выключен
 *
 * @var app\classes\BaseView $this
 */

use app\helpers\DateTimeZoneHelper;
use app\models\billing\EventFlag;
use kartik\form\ActiveForm;

//$currentHour = (new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format('H');
$isTimeCorrect = true;//$currentHour >= 19;
?>

<div class="row" style="padding-left: 15px;">
    <div class="col-md-8 alert alert-warning">
        <p>
            Изменения не синхронизируются в биллер! <br>
            Можно выполнять массовые операции по изменению префиксов.<br>
            После этого обязательно надо полностью синхронизировать все данные в биллер.
        </p>

        <?php
        $form = ActiveForm::begin();
        echo $this->render('//layouts/_submitButton', [
            'text' => ($isTimeCorrect) ? 'Синхронизировать NNP данные' : 'Синхронизировать NNP данные<br>(Доступно с 19:00 до 00:00)',
            'glyphicon' => 'glyphicon-off',
            'params' => [
                'name' => 'syncNnpAll',
                'disabled' => !$isTimeCorrect,
                'value' => 1,
                'class' => 'btn btn-success',
                'aria-hidden' => 'true',
                'onClick' => sprintf('return confirm("%s");', 'Полностью синхронизировать все данные в биллер?'),
            ],
        ]);
        ActiveForm::end();
        ?>
    </div>
    <div class="col-md-1">
        &nbsp;
    </div>
    <?php if (\Yii::$app->isRus()): ?>
    <div class="col-md-3 alert alert-info">
        <?php

        $out = [];

        $_countryCode = EventFlag::getOrNull('last_import_file_country_code');
        if ($_countryCode) {
            $country = \app\modules\nnp\models\Country::findOne(['code' => $_countryCode]);
            if ($country) {
                $out[] = 'Крайний импорт: страна: ' . \app\classes\Html::a($country->name, \yii\helpers\Url::to(['/nnp/import/step2', 'countryCode' => 348]));
            }
        }

        $_fileId = EventFlag::getOrNull('last_import_file_id');
        if ($_fileId) {
            $countryFile = \app\modules\nnp\models\CountryFile::findOne(['id' => $_fileId]);
            $out[] = 'Крайний импорт: файл: ' . \app\classes\Html::a($countryFile->name, \yii\helpers\Url::to(['/nnp/import/step3', 'countryCode' => 348, 'fileId' => $countryFile->id]));
        }

        $_importDate = EventFlag::getOrNull('last_import_file_date');
        if ($_importDate) {
            $out[] = 'Время запуска импорта: ' . DateTimeZoneHelper::getDateTime($_importDate);
        }

        $_userId = EventFlag::getOrNull('last_import_file_user_id');
        if ($_userId) {
            $out[] = 'Ползователь: ' . (\app\models\User::find()->where(['id' => $_userId])->select('name')->scalar() ?: '???');
        }

        if (EventFlag::getOrNull('is_nnp_sync_started')) {
            $out[] = \app\classes\Html::tag('span', 'Синхронизация данных с Европой в процессе ', ['class' => 'text-warning']);
        }elseif (EventFlag::getOrNull('is_nnp_sync_need')) {
            $out[] = \app\classes\Html::tag('span', 'Ожидается обновление данных с Европой', ['class' => 'text-muted']);
        }


        $_syncStartDate = EventFlag::getOrNull('is_nnp_sync_start_date');
        if ($_syncStartDate) {
            $out[] = 'Время запуска синхронизации: ' . DateTimeZoneHelper::getDateTime($_syncStartDate);
        }

        if (EventFlag::getOrNull('is_nnp_sync_ended')) {
            $out[] = \app\classes\Html::tag('span', 'Синхронизация данных с Европой завершена', ['class' => 'text-success']);
        }

        $_syncEndDate = EventFlag::getOrNull('is_nnp_sync_ended_date');
        if ($_syncEndDate) {
            $out[] = 'Время завершения синхронизации данных с Европой: ' . DateTimeZoneHelper::getDateTime($_syncEndDate);
        }

        echo implode(PHP_EOL . "<br>", $out);
        ?>

    </div>
    <?php endif; ?>
</div>
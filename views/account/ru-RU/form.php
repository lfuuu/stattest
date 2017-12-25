<?php

use app\classes\Html;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\Currency;
use app\models\GoodPriceType;
use app\models\Region;
use app\modules\uu\models\TariffStatus;
use kartik\widgets\ActiveForm;

/** @var ActiveForm $f */
/** @var \app\forms\client\AccountEditForm $model */
?>

<div class="max-screen">
    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'region')->dropDownList(Region::getList(), ['class' => 'select2']) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'timezone_name')->dropDownList(Region::getTimezoneList()) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'is_postpaid')->checkbox()->label('') ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'effective_vat_rate')
                ->textInput([
                    'disabled' => 'disabled',
                ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'nal')->dropDownList(ClientAccount::$nalTypes) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'currency')->dropDownList(Currency::map()) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'price_type')->dropDownList(GoodPriceType::getList()) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'pay_bill_until_days') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'credit', ['options' => ['id' => 'credit-size']]) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'anti_fraud_disabled')->checkbox()->label('') ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'lk_balance_view_mode')->dropDownList(ClientAccount::$balanceViewMode) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'account_version')->dropDownList(
                ClientAccount::$versions,
                $model->clientM->isAbleChangeAccountVersion() ? [] : ['disabled' => 'disabled']
            ) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'voip_credit_limit_day') ?>
        </div>
        <div class="col-sm-3">
            <?php
            $voipCreditLimitDayWhen = (array)$model->getModel()->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_WHEN);
            $voipCreditLimitDayValue = (array)$model->getModel()->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_VALUE);

            $result = [];

            if (count($voipCreditLimitDayWhen)) {
                $result[] = 'Дата пересчета: ' . array_shift($voipCreditLimitDayWhen);
            }

            if (count($voipCreditLimitDayValue)) {
                $result[] = 'Пересчитанное значение: ' . array_shift($voipCreditLimitDayValue);
            }

            echo $f->field($model, 'voip_is_day_calc')->checkbox()->label('');
            echo implode(Html::tag('br'), $result);
            ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'price_level')->dropDownList(ClientAccount::getPriceLevels()) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'uu_tariff_status_id')->dropDownList(TariffStatus::getList($serviceTypeId = null, $isWithEmpty = true)) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'voip_limit_mn_day') ?>
        </div>
        <div class="col-sm-3">
            <?php
            $voipCreditLimitDayMNWhen = (array)$model->getModel()->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_WHEN);
            $voipCreditLimitDayMNValue = (array)$model->getModel()->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_VALUE);

            $result = [];

            if (count($voipCreditLimitDayMNWhen)) {
                $result[] = 'Дата пересчета: ' . array_shift($voipCreditLimitDayMNWhen);
            }

            if (count($voipCreditLimitDayMNValue)) {
                $result[] = 'Пересчитанное значение: ' . array_shift($voipCreditLimitDayMNValue);
            }

            echo $f->field($model, 'voip_is_mn_day_calc')->checkbox()->label('');
            echo implode(Html::tag('br'), $result);
            ?>
        </div>
        <div class="col-sm-3">&nbsp;</div>
        <div class="col-sm-3">
            <?= $f->field($model, 'options[settings_advance_invoice]')
                ->dropDownList(ClientAccountOptions::$settingsAdvance)
                ->label($model->getAttributeLabel('settings_advance_invoice'))
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f
                ->field($model, 'options[mail_delivery_variant]')
                ->dropDownList([
                    'undefined' => 'Не определились / Не отправляем',
                    'payment' => 'Платная рассылка почтой РФ',
                    'by_self' => 'Самовывоз',
                ])
                ->label('Тип рассылки документов')
            ?>
        </div>
        <div class="col-sm-3">
            <?= $f
                ->field($model, 'options[black_list]')
                ->checkbox(['label' => 'Черный список'])
                ->label('')
            ?>
        </div>
        <div class="col-sm-3">
            <?= $f
                ->field($model, 'is_with_consignee')
                ->checkbox(['id' => 'with-consignee'])
                ->label('')
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f->field($model, 'address_post') ?>
        </div>
        <div class="col-sm-6">
            <?= $f->field($model, 'head_company') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f->field($model, 'address_post_real') ?>
        </div>
        <div class="col-sm-6">
            <?= $f->field($model, 'head_company_address_jur') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f->field($model, 'mail_who') ?>
        </div>
        <div class="col-sm-6">
            <?= $f->field($model, 'consignee', ['options' => ['id' => 'consignee']]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'form_type')->dropDownList(ClientAccount::$formTypes) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'stamp')->checkbox()->label('') ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'is_upd_without_sign')->checkbox()->label('') ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'type_of_bill')->checkbox()->label('') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f
                ->field($model, 'bill_rename1')
                ->radioList([
                    'yes' => 'Оказанные услуги по Договору',
                    'no' => 'Абонентская плата по Договору',
                ])
            ?>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-3"></div>
        <div class="col-sm-3"></div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f->field($model, 'bik') ?>
        </div>
        <div class="col-sm-6">
            <?= $f
                ->field($model, 'corr_acc')
                ->textInput([
                    'disabled' => 'disabled',
                ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= $f->field($model, 'pay_acc') ?>
        </div>
        <div class="col-sm-6">
            <?= $f
                ->field($model, 'bank_name')
                ->textInput([
                    'disabled' => 'disabled',
                ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
            <?= $f
                ->field($model, 'bank_city')
                ->textInput([
                    'disabled' => 'disabled',
                ])
            ?>
        </div>
    </div>
</div>
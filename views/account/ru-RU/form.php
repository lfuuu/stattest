<?php

use app\classes\Html;
use app\models\ClientAccountOptions;
use app\models\Region;
use app\models\PriceType;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\builder\Form;
use app\models\ClientAccount;
use app\models\Currency;

/** @var ActiveForm $f */
/** @var \app\forms\client\AccountEditForm $model */
?>

<div class="row col-sm-12" style="width: 1100px;">
    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'region')->dropDownList(Region::getList(), ['class' => 'select2']) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'timezone_name')->dropDownList(Region::getTimezoneList()) ?>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-3"></div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'nal')->dropDownList(ClientAccount::$nalTypes) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'currency')->dropDownList(Currency::map()) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'price_type')->dropDownList(PriceType::getList()) ?>
        </div>
        <div class="col-sm-3"></div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $f->field($model, 'credit', ['options' => ['id' => 'credit-size']]) ?>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-3">
            <?= $f->field($model, 'lk_balance_view_mode')->dropDownList(ClientAccount::$balanceViewMode) ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'account_version')->dropDownList(ClientAccount::$versions) ?>
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

            echo $f->field($model, 'voip_is_day_calc', ['options' => ['style' => 'margin-top: 45px']])->checkbox();
            echo implode(Html::tag('br'), $result);
            ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'voip_credit_limit') ?>
        </div>
        <div class="col-sm-3">
            <?=$f->field($model, 'anti_fraud_disabled', ['options' => ['style' => 'margin-top: 45px']])->checkbox() ?>
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

            echo $f->field($model, 'voip_is_mn_day_calc', ['options' => ['style' => 'margin-top: 45px']])->checkbox();
            echo implode(Html::tag('br'), $result);
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
                ->field($model, 'options[black_list]', ['options' => ['style' => 'margin-top: 30px']])
                ->checkbox([
                    'label' => 'Черный список',
                ])
            ?>
        </div>
        <div class="col-sm-3">
            <?= $f
                ->field($model, 'is_with_consignee', ['options' => ['style' => 'margin-top: 30px']])
                ->checkbox(['id' => 'with-consignee'])
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
            <?= $f->field($model, 'stamp', ['options' => ['style' => 'margin-top: 30px']])->checkbox() ?>
        </div>
        <div class="col-sm-3">
            <?= $f->field($model, 'is_upd_without_sign', ['options' => ['style' => 'margin-top: 30px']])->checkbox() ?>
        </div>
        <div class="col-sm-3"></div>
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
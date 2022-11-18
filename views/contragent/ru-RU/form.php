<?php

use app\classes\Html;
use app\dao\PartnerDao;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Country;
use app\models\Language;
use app\models\SaleChannel;
use kartik\builder\Form;
use kartik\widgets\Select2;

/**
 * @var $f kartik\widgets\ActiveForm
 * @var $model \app\forms\client\ContragentEditForm
 */

$codeOpfList = ['0' => ''] + \app\models\CodeOpf::getList($isWithEmpty = false);
?>

<div class="row max-screen">
    <div class="col-sm-6">
        <?= $f->field($model, 'country_id')->dropDownList(Country::getList()); ?>
    </div>
    <div class="col-sm-6">
        <?= $f->field($model, 'lang_code')->dropDownList(Language::getList()); ?>
    </div>
</div>

<div class="col-sm-8 text-center bottom-indent">
    <div id="type-select">
        <div class="btn-group">
            <button type="button" class="btn btn-default"
                    data-tab="#legal"><?= $model->getAttributeLabel('legalTypeLegal') ?></button>
            <button type="button" class="btn btn-default"
                    data-tab="#ip"><?= $model->getAttributeLabel('legalTypeIp') ?></button>
            <button type="button" class="btn btn-default"
                    data-tab="#person"><?= $model->getAttributeLabel('legalTypePerson') ?></button>
        </div>
    </div>
</div>
<?= Html::activeHiddenInput($model, 'legal_type') ?>
<?= Html::activeHiddenInput($model, 'super_id') ?>
<div id="fs-tabs" class="row max-screen">
    <div id="legal" class="tab-pane col-sm-12">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'options' => ['class' => 'percent100'],
            'columnOptions' => ['class' => 'col-sm-6'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'name' => [],
                'address_jur' => [],
                'name_full' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-6'],
            'options' => ['class' => 'pull-left percent50 block-right-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'inn' => [],
                'kpp' => [],
                'okvd' => [],
                'ogrn' => [],
                'opf_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $codeOpfList,
                ],
                'okpo' => [],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'options' => ['class' => 'percent50 block-left-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'tax_regime' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => ClientContragent::$taxRegtimeTypes,
                    'container' => []
                ],
                'inn_euro' => [],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'options' => ['class' => 'percent50 block-left-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'tax_registration_reason' => [],
                'org_type' => [],

                'position' => [],
                'fio' => [],
            ],
        ]);
        ?>
    </div>

    <div id="ip" class="tab-pane col-sm-12">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'options' => ['class' => 'pull-left percent50 block-right-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'last_name' => [],
                'first_name' => [],
                'middle_name' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['class' => 'percent50 block-left-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'address_jur' => [],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['class' => 'percent50 block-left-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'tax_regime' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => ClientContragent::$taxRegtimeTypes,
                    'container' => ['class' => 'percent50']
                ],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'options' => ['class' => 'clearix percent50 block-right-indent'],
            'columnOptions' => ['class' => 'col-sm-6'],
            'attributeDefaults' => [
                'container' => [
                    'type' => Form::INPUT_TEXT
                ],
            ],
            'attributes' => [
                'inn' => [],
                'ogrn' => [],
                'opf_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $codeOpfList,
                ],
                'okpo' => [],
            ],
        ]);
        ?>
    </div>

    <div id="person" class="tab-pane col-sm-12">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'options' => ['class' => 'pull-left percent50 block-right-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'last_name' => [],
                'first_name' => [],
                'middle_name' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-6'],
            'options' => ['class' => 'percent50 block-left-indent block-right-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'passport_serial' => [],
                'passport_number' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['class' => 'percent50 block-left-indent block-right-indent'],
            'attributeDefaults' => [
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'passport_date_issued' => [
                    'type' => Form::INPUT_WIDGET,
                    'widgetClass' => 'app\widgets\DateControl',
                    'convertFormat' => true,
                    'options' => [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'startDate' => '-40y',
                            'endDate' => '+1y',
                        ],
                    ],
                ],
                'birthday' => [
                    'type' => Form::INPUT_WIDGET,
                    'widgetClass' => 'app\widgets\DateControl',
                    'convertFormat' => true,
                    'options' => [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'startDate' => '-100y',
                            'endDate' => '0y',
                        ],
                    ],
                ],
                'passport_issued' => ['columnOptions' => ['colspan' => 2]],
                ['type' => Form::INPUT_RAW],
                'registration_address' => ['columnOptions' => ['colspan' => 2]],
                'birthplace' => ['columnOptions' => ['colspan' => 2]],
            ],
        ]);
        ?>
    </div>

    <div class="partner-block">

        <div class="col-sm-6">
            <?= $f->field($model, 'comment')
                ->textarea(['style' => 'height: 100px;']) ?>
        </div>

        <div class="col-sm-3 bottom-indent right-indent">
            <?=
            $f->field($model, 'sale_channel_id')
                ->widget(Select2::class, [
                    'data' => SaleChannel::getList($isWithEmpty = true),
                ])
            ?>
        </div>

        <div class="col-sm-3 bottom-indent right-indent">
            <?=
            $f->field($model, 'branch_code')
                ->textInput()
            ?>
        </div>

    </div>
</div>
<?php
use yii\helpers\Html;
use kartik\builder\Form;
use app\models\ClientContragent;
use app\models\ClientContract;
use app\models\Country;
use app\models\NewSaleChannel;
use kartik\widgets\Select2;
use app\models\Business;

/**
 * @var $f kartik\widgets\ActiveForm
 */

?>

<div class="row" style="width: 1100px;">
    <div class="col-sm-6">
        <?= $f->field($model, 'country_id')->dropDownList(Country::getList()); ?>
    </div>
</div>

<div class="col-sm-8" style="margin-bottom: 20px; text-align: center;">
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
<div id="fs-tabs" class="row" style="width: 1100px;">
    <?php
    echo '<div id="legal" class="tab-pane col-sm-12">';
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 2,
        'options' => ['style' => 'width:100%;'],
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
        'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
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
                'items' => \app\models\CodeOpf::getList(),
            ],
            'okpo' => [],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 1,
        'options' => ['style' => 'width:50%; padding-left: 15px; '],
        'attributeDefaults' => [
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => [
            'tax_regime' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ClientContragent::$taxRegtimeTypes,
                'container' => ['style' => 'width:50%;']
            ],
            'position' => [],
            'fio' => [],
        ],
    ]);
    echo '</div>';

    echo '<div id="ip" class="tab-pane col-sm-12">';
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 1,
        'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
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
        'options' => ['style' => 'width:50%; padding-left: 15px;'],
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
        'options' => ['style' => 'width:50%; padding-left: 15px;'],
        'attributeDefaults' => [
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => [
            'tax_regime' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ClientContragent::$taxRegtimeTypes,
                'container' => ['style' => 'width:50%;']
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 2,
        'options' => ['style' => 'clear:both; width:50%; padding-right: 15px;'],
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
                'items' => \app\models\CodeOpf::getList(),
            ],
            'okpo' => [],
        ],
    ]);

    echo '</div>';

    echo '<div id="person" class="tab-pane col-sm-12">';
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 1,
        'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
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
        'options' => ['style' => 'width:50%; padding-left: 15px; padding-right: 15px;'],
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
        'options' => ['style' => 'width:50%; padding-left: 15px; padding-right: 15px;'],
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
            ['type' => Form::INPUT_RAW],
        ],
    ]);
    echo '</div>';
    ?>
    <div style="width: 100%;">
        <div class="col-sm-6">
            <?= $f->field($model, 'comment')->textarea(['style' => 'height: 100px;']) ?>
        </div>
        <div class="col-sm-3" style="padding-bottom: 15px;">
            <?php
                $partners = [];
                $cts = ClientContract::find()->andWhere(['business_id' => Business::PARTNER])->all();
                foreach($cts as $ct){
                    $accounts = $ct->getAccounts();
                    if($accounts){
                        $partners[$accounts[0]->id] = $ct->getContragent()->name;
                    }
                }
            ?>
            <?=
            $f->field($model, 'partner_id')->widget(Select2::className(), [
                'data' => $partners,
                'options' => ['placeholder' => 'Начните вводить название'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])
            ?>

        </div>
        <div class="col-sm-3" style="padding-right: 30px; padding-bottom: 15px;">
            <?=
            $f->field($model, 'sale_channel_id')->widget(Select2::className(), [
                'data' => NewSaleChannel::getList(),
                'options' => ['placeholder' => 'Начните вводить название'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])
            ?>
        </div>
    </div>

</div>


<script>
    $(document).ready(function () {
        var b = $('#type-select .btn[data-tab="#' + $('#contragenteditform-legal_type').val() + '"]');
        if (b.length < 1) {
            b = $('#type-select .btn').first();
            $('#contragenteditform-legal_type').val(b.data('tab').replace("#", ""));
        }
        b.addClass('btn-primary').removeClass('btn-default');
        $('.tab-pane').hide();
        $($('#type-select .btn-primary').data('tab')).show();

        $('#contragenteditform-country_id').on('change', function () {
            var form = $(this).closest('form');
            $('<input type="hidden" name="notSave" value="1">').appendTo(form);
            form.submit();
        });
    });

    $('#type-select .btn').on('click', function () {
        var oldT = $('#type-select .btn-primary').data('tab');
        var newT = $(this).data('tab');
        $(oldT + ' .form-control').each(function () {
            $(newT + ' .form-control[name="' + $(this).attr('name') + '"]').val($(this).val());
        });

        $('#contragenteditform-legal_type').val($(newT).attr('id'));

        $('#type-select .btn').removeClass('btn-primary').addClass('btn-default');
        $(this).addClass('btn-primary');
        $('.tab-pane').hide();
        $(newT).show();
    });

    $('#legal #contragenteditform-name').on('blur', function () {
        if ($('#legal #contragenteditform-name_full').val() == '')
            $('#legal #contragenteditform-name_full').val($(this).val());
    });
    $('#legal #contragenteditform-name_full').on('blur', function () {
        if ($('#legal #contragenteditform-name').val() == '')
            $('#legal #contragenteditform-name').val($(this).val());
    });

    $('#legal #contragenteditform-address_jur').on('blur', function () {
        if ($('#legal #contragenteditform-address_post').val() == '')
            $('#legal #contragenteditform-address_post').val($(this).val());
    });
    $('#legal #contragenteditform-address_post').on('blur', function () {
        if ($('#legal #contragenteditform-address_jur').val() == '')
            $('#legal #contragenteditform-address_jur').val($(this).val());
    });

</script>

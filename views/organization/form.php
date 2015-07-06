<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use yii\helpers\ArrayHelper;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use app\forms\organization\OrganizationForm;
use app\models\Country;
use app\models\Person;

/** @var $model OrganizationForm */
?>

<?php if (!empty($title)): ?>
<h2>
    <?= $title; ?>
</h2>
<?php endif; ?>

<script type="text/javascript" src="/js/behaviors/organization.js"></script>

<div class="container" style="width: 100%; padding-top: 20px;">
    <?php
    $form_options = [
        'type' => ActiveForm::TYPE_VERTICAL,
        'id' => 'OrganizationFrm',
    ];
    if ($mode == 'duplicate')
        $form_options['action'] = '/organization/add';
    $form = ActiveForm::begin($form_options);
    ?>

    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'firma')
                        ->textInput(['readonly' => $mode == 'duplicate' ? true : false])
                        ->label('Код организации ("mcn", "ooomcn" etc)');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    $field = $form->field($model, 'country_id')
                        ->dropDownList(
                            ArrayHelper::map(
                                Country::find()->where(['in_use' => 1])->orderBy('code desc')->all(),
                                'code',
                                'name'
                            ),
                            [
                                'data-action' => 'applyCountry',
                                'data-target' => '#applyTaxSystem',
                                'readonly' => $mode == 'duplicate' ? true : false
                            ]
                        );
                    echo $field->label('Страна');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    $langs = ArrayHelper::getColumn(
                        Country::find()->select('lang')->distinct()->where(['in_use' => 1])->orderBy('code desc')->all(),
                        'lang'
                    );
                    foreach ($langs as $key => $value):
                        $langs[$value] = $value;
                        unset($langs[$key]);
                    endforeach;

                    echo $form->field($model, 'lang_code')
                        ->dropDownList($langs, ['readonly' => $mode == 'duplicate' ? true : false])
                        ->label('Язык');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'actual_from')
                        ->widget(DateControl::classname(), [
                            'type' => DateControl::FORMAT_DATE,
                            'ajaxConversion' => false,
                            'disabled' => $mode == 'edit',
                            'options' => [
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'orientation' => 'top left',
                                    'startDate' =>  'today',
                                ]
                            ]
                        ])
                        ->label('Дата активации');
                    ?>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name')->label('Краткое название');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'legal_address')->label('Юридический адрес');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'full_name')->label('Полное название');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_address')->label('Почтовый адрес');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'director_id')
                        ->dropDownList(
                            ArrayHelper::map(Person::find()->all(), 'id', 'name_nominative'),[
                                'prompt' => 'Выберите директора',
                                'style' => 'width: 80%;',
                            ]
                        )
                        ->label('Директор');
                    ?>
                    <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 150px;">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить
                    </a>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'accountant_id')
                        ->dropDownList(
                            ArrayHelper::map(Person::find()->all(), 'id', 'name_nominative'), [
                                'prompt' => 'Выберите бухгалтера',
                                'style' => 'width: 80%;',
                            ]
                        )
                        ->label('Главный бухгалтер');
                    ?>
                    <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 150px;">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить
                    </a>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

    <fieldset style="width: 50%; padding-right: 15px; float: left;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'tax_system')
                        ->dropDownList([], [
                            'id' => 'applyTaxSystem',
                            'data-action' => 'applyTaxSystem',
                            'data-target' => '#vatRate',
                            'data-value' => $model->tax_system,
                        ])
                        ->label('Система налогообложения');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'vat_rate')
                        ->textInput([
                            'id' => 'vatRate',
                            'data-value' => $model->vat_rate,
                        ])
                        ->label('Ставка НДС');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'tax_registration_id')->label('ИНН');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'tax_registration_reason')->label('КПП');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'registration_id')->label('ОГРН');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset style="width: 50%; padding-left: 15px;">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_account')->label('Расчетный счет');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_bik')->textInput([
                        'class' => 'search-info',
                        'data-type' => 'rpc-find-bank-1c',
                        'data-param' => 'findBik',
                    ])->label('БИК');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_swift')->label('SWIFT');
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_name')->label('Название банка');
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_correspondent_account')->label('Кор. счет');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <div style="height: 25px;">&nbsp;</div>

    <fieldset style="width: 50%; padding-right: 15px; float: left;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_phone')->label('Телефон');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_fax')->label('Факс');
                    ?>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_email')->input('email')->label('E-mail');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_site')->label('Сайт URL');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset style="width: 50%; padding-left: 15px; ">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'logo_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('ORGANIZATION_LOGO_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать логотип',
                                'data-source' => Yii::$app->params['ORGANIZATION_LOGO_DIR'],
                                'data-target' => '#full_frm_logo_file_name',
                                'class' => 'image_preview_select',
                            ]
                        )
                        ->label('Логотип компании');
                    ?>
                    <div id="full_frm_logo_file_name" class="image_preview"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'stamp_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('STAMP_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать печать',
                                'data-source' => Yii::$app->params['STAMP_DIR'],
                                'data-target' => '#full_frm_stamp_file_name',
                                'class' => 'image_preview_select',
                            ]
                        )
                        ->label('Печать компании');
                    ?>
                    <div id="full_frm_stamp_file_name" class="image_preview"></div>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="row">
        <div class="col-sm-12" style="padding-top: 30px; padding-left: 30px;">
            <?php
            echo Form::widget([
                'model' => $model,
                'form' => $form,
                'attributes' => [
                    'organization_id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'organization_id')],
                    'actions' => [
                        'type' => Form::INPUT_RAW,
                        'value' =>
                            '<div class="col-md-12" style="text-align: right;">' .
                            Html::button('Отменить', [
                                'class' => 'btn btn-link modal-form-close',
                                'style' => 'margin-right: 15px;',
                                'onClick' => 'history.back(-1)',
                            ]) .
                            Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) .
                            '</div>'
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

    <?php
    ActiveForm::end();
    ?>
</div>

<script type="text/javascript">
var frm = 'OrganizationFrm',
    search_result = {
        'findBik': function(data) {
            var fields = {
                'corr_acc' : 'bank_correspondent_account',
                'bank_name' : 'bank_name'
            };

            for (var key in data) {
                if (data.hasOwnProperty(key) && fields[key]) {
                    $('#' + frm).find('input[name*="' + fields[key] + '"]').val(data[key]);
                }
            }
        }
    };

jQuery(function() {
    $('.image_preview_select')
        .change(function() {
            var $source = $(this).data('source'),
                $value = $(this).find('option:selected').val(),
                $image = ($value != '' ? $('<img />').attr('src', $source + $value) : false);

            if ($(this).data('target'))
                $($(this).data('target')).html($value ? $image : '');
        })
        .trigger('change');

    $('input.search-info').bind('keyup change', function() {
        var that = $(this);

        $.ajax({
            url: '/data/' + that.data('type') + '/?value=' + that.val(),
            dataType: 'json',
            beforeSend: function() {
                that.addClass('ui-autocomplete-loading');
            },
            success: function(result) {
                if ($.isFunction(search_result[ that.data('param') ]))
                    search_result[ that.data('param') ]($.parseJSON(result));
                that.removeClass('ui-autocomplete-loading');
            }
        });
    });
});
</script>

<style type="text/css">
.image_preview {
    position: relative;
    border: 1px solid;
    background-color: #FFFFFF;
    text-align: center;
    vertical-align: bottom;
    width: 250px;
    height: 250px;
    margin: 0 auto;
    overflow: hidden;
}
    .image_preview img {
        position: absolute;
        margin: auto;
        top: -200px;
        bottom: -200px;
        left: -200px;
        right: -200px;
    }
.ui-autocomplete-loading {
    background: white url('images/ajax-loader-small.gif') 98% center no-repeat;
}
</style>
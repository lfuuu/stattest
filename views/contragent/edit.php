<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;

?>

<h1>
    Редактировать контрагента
</h1>


<?php
$f = ActiveForm::begin();
$taxRegtimeItems = ['full' => 'Полный', 'simplified' => 'Упрощенный'];
?>

<fieldset style="margin-bottom: 20px; text-align: center;">
    <div class="row">
        <div class="col-sm-8">
            <div class="col-sm-12">
                <div class="btn-group" id="type-select">
                    <button type="button" class="btn btn-default" data-tab="#legal">Юр. лицо</button>
                    <button type="button" class="btn btn-default" data-tab="#ip">ИП</button>
                    <button type="button" class="btn btn-default" data-tab="#person">Физ. лицо</button>
                    <?=Html::activeHiddenInput($model, 'legal_type') ?>
                </div>
            </div>
        </div>
    </div>
</fieldset>

<div id="fs-tabs" style="width: 1100px;">
    <?php
    echo '<div id="legal" class="tab-pane">';
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-6'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'name' => [],
                'address_jur' => [],
                'name_full' => [],
                'address_post' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-6'],
            'options' => ['style'=>'width:50%; padding-right: 15px; float: left;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'inn' => [],
                'kpp' => [],
                'okvd' => [],
                'ogrn' => [],
                'opf' => [],
                'okpo' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'options' => ['style'=>'width:50%; padding-left: 15px; '],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'tax_regime' => ['type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $taxRegtimeItems,
                    'container' => ['class' => 'col-sm-6']
                ],
                'position' => [],
                'fio' => [],
            ],
        ]);

    echo '</div>';

    echo '<div id="ip" class="tab-pane">';
        echo Form::widget([
            'model' => $person,
            'form' => $f,
            'columns' => 1,
            'options' => ['style'=>'width:50%; padding-right: 15px; float: left;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'last_name' => [],
                'first_name' => [],
                'middle_name' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $person,
            'form' => $f,
            'columns' => 1,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['style'=>'width:50%; padding-left: 15px;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'registration_address' => [],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'options' => ['style'=>'width:50%; padding-left: 15px;'],
            'columnOptions' => ['class' => 'col-sm-12'],
            'attributeDefaults' => [
                'container' => [
                    'class' => 'col-sm-12',
                    'type' => Form::INPUT_TEXT
                ],
            ],
            'attributes' => [
                'address_post' => [],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['style'=>'width:50%; padding-left: 15px;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-6'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'tax_regime' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $taxRegtimeItems],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'options' => ['style'=>'float:left; width:50%; padding-right: 15px;'],
            'columnOptions' => ['class' => 'col-sm-6'],
            'attributeDefaults' => [
                'container' => [
                    'class' => 'col-sm-12',
                    'type' => Form::INPUT_TEXT
                ],
            ],
            'attributes' => [
                'inn' => [],
                'ogrn' => [],
                'opf' => [],
                'okpo' => [],
            ],
        ]);

    echo '</div>';

    echo '<div id="person" class="tab-pane">';
        echo Form::widget([
            'model' => $person,
            'form' => $f,
            'columns' => 1,
            'options' => ['style'=>'width:50%; padding-right: 15px; float: left;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'last_name' => [],
                'first_name' => [],
                'middle_name' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $person,
            'form' => $f,
            'columns' => 2,
            'columnOptions' => ['class' => 'col-sm-6'],
            'options' => ['style'=>'width:50%; padding-left: 15px; padding-right: 15px;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'passport_serial' => [],
                'passport_number' => [],
            ],
        ]);
        echo Form::widget([
            'model' => $person,
            'form' => $f,
            'columns' => 1,
            'columnOptions' => ['class' => 'col-sm-12'],
            'options' => ['style'=>'width:50%; padding-left: 15px; padding-right: 15px;'],
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'passport_date_issued' => ['columnOptions' => ['class' => 'col-sm-6'],],
                'passport_issued' => [],
                'registration_address' => [],
            ],
        ]);

    echo '</div>';
    ?>
</div>
    <div class="row" style="clear: both;">
        <div class="col-sm-6">
            <div class="col-sm-12">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-default', 'id' => 'buttonSave']); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<script>
    $(document).ready(function(){
        $('#type-select .btn[data-tab="#'+ $('#type-select input').val() +'"]').addClass('btn-primary').removeClass('btn-default');
        $('.tab-pane').hide();
        $($('#type-select .btn-primary').data('tab')).show();

        $('#type-select .btn').on('click', function () {
            var oldT = $('#type-select .btn-primary').data('tab');
            var newT = $(this).data('tab');
            $(oldT+' .form-control').each(function(){
                $(newT+' .form-control[name="'+ $(this).attr('name') +'"]').val($(this).val());
            });

            $('#clientcontragent-legal_type').val($(newT).attr('id'));

            $('#type-select .btn').removeClass('btn-primary').addClass('btn-default');
            $(this).addClass('btn-primary');
            $('.tab-pane').hide();
            $(newT).show();
        });

       $('#buttonSave').on('click', function(e){
           $('#type-select .btn').not('.btn-primary').each(function(){
               $($(this).data('tab')).remove();
           });
           return true;
       });
    });

    $('#legal #clientcontragent-name').on('keyup', function () {
        if($('#legal #clientcontragent-name_full').val() == '')
            $('#legal #clientcontragent-name_full').val($(this).val());
    });
    $('#legal #clientcontragent-name_full').on('keyup', function () {
        if($('#legal #clientcontragent-name').val() == '')
            $('#legal #clientcontragent-name').val($(this).val());
    });

    $('#legal #clientcontragent-address_jur').on('keyup', function () {
        if($('#legal #clientcontragent-address_post').val() == '')
            $('#legal #clientcontragent-address_post').val($(this).val());
    });
    $('#legal #clientcontragent-address_post').on('keyup', function () {
        if($('#legal #clientcontragent-address_jur').val() == '')
            $('#legal #clientcontragent-address_jur').val($(this).val());
    });
</script>

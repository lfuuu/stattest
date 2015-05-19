<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\builder\Form;

?>

<h1>
    Редактировать договора
</h1>


<?php
$f = ActiveForm::begin();
$contractTypes = ['full' => 'Полный (НДС 18%)', 'simplified' => 'без НДС'];
?>

<div style="width: 1100px;">
    <?php

    echo '<div>';
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'options' => ['style' => 'padding-right:30px;'],
        'attributeDefaults' => [
            'container' => ['class' => 'col-sm-6'],
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => [
            'organization' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $model->getOrganizationsList()],
        ],
    ]);
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 2,
        'attributeDefaults' => [
            'container' => ['class' => 'col-sm-12'],
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => [
            'manager' => [
                'type' =>Form::INPUT_RAW,
                'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['manager'] . '</label>'
                    . Select2::widget([
                        'model' => $model,
                        'attribute' => 'manager',
                        'data' => $model->getManagersList(),
                        'options' => ['placeholder' => 'Начните воодить фамилию'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                    . '</div>'
            ],
            'account_manager' => [
                'type' =>Form::INPUT_RAW,
                'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['account_manager'] . '</label>'
                    . Select2::widget([
                        'model' => $model,
                        'attribute' => 'account_manager',
                        'data' => $model->getAccountManagersList(),
                        'options' => ['placeholder' => 'Начните воодить фамилию'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                    . '</div>'
            ],
            'signer_position' => [],
            'signer_name' => [],
            'signer_positionV' => [],
            'signer_nameV' => [],
        ],
    ]);

    echo '</div>';
    ?>


</div>
<div class="row" style="clear: both;">
    <div class="col-sm-6">
        <div class="col-sm-12 form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-default', 'id' => 'buttonSave']); ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12 form-group">
            <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientContragent:' . $model->id . ', ClientPerson:' . $model->id . '})']); ?>
            <span>История изменений</span>
        </div>
    </div>
</div>


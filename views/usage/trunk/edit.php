<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;
use app\models\UsageTrunkSettings;
use app\forms\usage\UsageTrunkSettingsForm;
use app\forms\usage\UsageTrunkCloseForm;
use app\models\billing\Number;
use app\models\billing\Pricelist;
use app\forms\usage\UsageTrunkSettingsAddForm;
use app\forms\usage\UsageTrunkSettingsEditForm;
use app\forms\usage\UsageTrunkSettingsDeleteForm;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $usage \app\models\UsageTrunk */
/** @var $model \app\forms\usage\UsageTrunkEditForm */
/** @var $origination UsageTrunkSettings[] */
/** @var $termination UsageTrunkSettings[] */
/** @var $destination UsageTrunkSettings[] */

$srcNumbers = ['' => '-- Любой номер -- '] + Number::dao()->getList(Number::TYPE_SRC, $usage->connection_point_id);
$dstNumbers = ['' => '-- Любой номер -- '] + Number::dao()->getList(Number::TYPE_DST, $usage->connection_point_id);
$termPricelists = ['' => '-- Прайслист -- '] + Pricelist::dao()->getList('operator');
$origPricelists = ['' => '-- Прайслист -- '] + Pricelist::dao()->getList('client');

?>
<legend>
    <?= Html::a($clientAccount->company, '/?module=clients&id='.$clientAccount->id) ?> ->
    <?= Html::a('Телефония Транки', '/?module=services&action=trunk_view') ?> ->
    <?= Html::a($usage->trunk_name, Url::to(['edit', 'id' => $usage->id])) ?>
</legend>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        ['type' => Form::INPUT_RAW, 'value' => '
            <div class="form-group">
                <label class="control-label">Точка подключения</label>
                <input type="text" class="form-control" value="' . $usage->connectionPoint->name . '" readonly>
            </div>
        '],
        ['type' => Form::INPUT_RAW, 'value' => '
            <div class="form-group">
                <label class="control-label">Страна</label>
                <input type="text" class="form-control" value="' . $clientAccount->country->name . '" readonly>
            </div>
        '],
        ['type' => Form::INPUT_RAW, 'value' => '
            <div class="form-group">
                <label class="control-label">Валюта</label>
                <input type="text" class="form-control" value="' . $clientAccount->currency . '" readonly>
            </div>
        '],
    ],
]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'trunk_name' => ['type' => Form::INPUT_TEXT],
        'actual_from' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(), 'options' => ['autoWidget' => false, 'readonly' => true]],
        'actual_to' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(), 'options' => ['autoWidget' => false, 'readonly' => true]],
    ],
]);


echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        '' => ['type' => Form::INPUT_RAW],
        'orig_enabled' => ['type' => Form::INPUT_CHECKBOX],
        'orig_min_payment' => ['type' => Form::INPUT_TEXT],
    ],
]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        '' => ['type' => Form::INPUT_RAW],
        'term_enabled' => ['type' => Form::INPUT_CHECKBOX],
        'term_min_payment' => ['type' => Form::INPUT_TEXT],
    ],
]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        '' => ['type' => Form::INPUT_RAW],
        'description' => ['type' => Form::INPUT_TEXT],
        'operator_id' => ['type' => Form::INPUT_TEXT],
    ],
]);

if ($usage->isActive()):

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-12">' .
                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'onclick' => "jerasoftSubmitForm('edit')"]) .
                    '</div>'
            ],
        ],
    ]);

endif;

echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
ActiveForm::end();
?>
<script>
    function jerasoftSubmitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>')[0].submit();
    }
    $('.form-reload').change(function() {
        jerasoftSubmitForm('default');
    });
</script>

<?php if($usage->orig_enabled): ?>
    <h2>Оригинация:</h2>
    <table class="table table-condensed table-striped">
        <tr>
            <th width="33%">A номер</th>
            <th width="33%">B номер</th>
            <th width="33%">Прайслист</th>
            <th></th>
        </tr>
        <?php foreach ($origination as $rule): ?>
            <?php
            $formModel = new UsageTrunkSettingsEditForm();
            $formModel->setAttributes($rule->attributes, false);

            $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
            echo Html::activeHiddenInput($formModel, 'id');
            ?>
            <tr>
                <td><?= $form->field($formModel, 'src_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($srcNumbers, ['class' => 'select2']) ?></td>
                <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
                <td><?= $form->field($formModel, 'pricelist_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($origPricelists, ['class' => 'select2']) ?></td>
                <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
            </tr>
            <?php
            ActiveForm::end();
            ?>
        <?php endforeach; ?>
        <?php
        if ($usage->isActive()):
            $formModel = new UsageTrunkSettingsAddForm();
            $formModel->usage_id = $usage->id;
            $formModel->type = UsageTrunkSettings::TYPE_ORIGINATION;

            $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
            echo Html::activeHiddenInput($formModel, 'usage_id');
            echo Html::activeHiddenInput($formModel, 'type');
        ?>
        <tr>
            <td colspan="3"></td>
            <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
            <td></td>
        </tr>
        <?php
            $form->end();
        endif;
        ?>
    </table>
<?php endif; ?>

<?php if($usage->term_enabled): ?>
    <h2>Терминация:</h2>
    <table class="table table-condensed table-striped">
        <tr>
            <th width="33%">A номер</th>
            <th width="33%">B номер</th>
            <th width="33%">Прайслист</th>
            <th></th>
            <th></th>
        </tr>
        <?php foreach ($termination as $rule): ?>
            <?php
            $formModel = new UsageTrunkSettingsEditForm();
            $formModel->setAttributes($rule->attributes, false);

            $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
            echo Html::activeHiddenInput($formModel, 'id');
            ?>
            <tr>
                <td><?= $form->field($formModel, 'src_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($srcNumbers,  ['class' => 'select2']) ?></td>
                <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
                <td><?= $form->field($formModel, 'pricelist_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($termPricelists, ['class' => 'select2']) ?></td>
                <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
            </tr>
            <?php
            $form->end();
            ?>
        <?php endforeach; ?>
        <?php
        if ($usage->isActive()):
            $formModel = new UsageTrunkSettingsAddForm();
            $formModel->usage_id = $usage->id;
            $formModel->type = UsageTrunkSettings::TYPE_TERMINATION;

            $form = ActiveForm::begin();
            echo Html::activeHiddenInput($formModel, 'usage_id');
            echo Html::activeHiddenInput($formModel, 'type');
        ?>
        <tr>
            <td colspan="3"></td>
            <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
        </tr>
        <?php
            $form->end();
        endif;
        ?>
    </table>
<?php endif; ?>

<h2>Направления:</h2>
<table class="table table-condensed table-striped">
    <tr>
        <th width="100%">B номер</th>
        <th></th>
    </tr>
    <?php foreach ($destination as $rule): ?>
        <?php
        $formModel = new UsageTrunkSettingsEditForm();
        $formModel->setAttributes($rule->attributes, false);

        $form = ActiveForm::begin();
        echo Html::activeHiddenInput($formModel, 'id');
        ?>
        <tr>
            <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
            <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
        </tr>
        <?php
        $form->end();
        ?>
    <?php endforeach; ?>
    <?php
    if ($usage->isActive()):
        $formModel = new UsageTrunkSettingsAddForm();
        $formModel->usage_id = $usage->id;
        $formModel->type = UsageTrunkSettings::TYPE_DESTINATION;

        $form = ActiveForm::begin();
        echo Html::activeHiddenInput($formModel, 'usage_id');
        echo Html::activeHiddenInput($formModel, 'type');
    ?>
    <tr>
        <td></td>
        <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
    </tr>
    <?php
        $form->end();
    endif;
    ?>
</table>

<?php if ($usage->isActive()): ?>
    <h2><span style="border-bottom: 1px dotted #000; cursor: pointer;" onclick="$('#div_close').toggle()">Отключение услуги:</span></h2>
    <div id="div_close" style="display: none">
        <?php
        $formModel = new UsageTrunkCloseForm();
        $formModel->usage_id = $usage->id;

        $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
        echo Html::activeHiddenInput($formModel, 'usage_id');
        echo Form::widget([
            'model' => $formModel,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'actual_to' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
                'x2' => ['type' => Form::INPUT_RAW, 'value' => '
                                <div style="padding-top: 22px">
                                    ' . Html::submitButton('Установить дату отключения', ['class' => 'btn btn-primary']) . '
                                </div>
                            '],
            ],
        ]);
        $form->end();
        ?>
    </div>
<?php endif; ?>
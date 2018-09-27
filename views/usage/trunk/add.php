<?php
use app\classes\BaseView;
use app\classes\Html;
use app\models\billing\Trunk;
use app\models\Region;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \app\models\ClientAccount $clientAccount */
/** @var \app\forms\usage\UsageTrunkEditForm $model */
/** @var \app\models\UsageTrunk $usage */
/** @var BaseView $this */

$trunks = ['' => '-- Выберите Транк -- '] + Trunk::dao()->getList(['serverIds' => $model->connection_point_id]);

echo Html::formLabel('Добавление транка');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Телефония Транки', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view'])],
        'Добавление транка'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    $this->registerJsVariables([
        'editFormId' => $form->getId(),
        'tariffEditFormId' => '',
    ], 'usage');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'connection_point_id')
                    ->dropDownList(Region::getList($isWithEmpty = true, $countryId = null, [Region::TYPE_HUB, Region::TYPE_POINT, Region::TYPE_NODE]), ['class' => 'select2 form-reload'])
                ?>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label">Страна</label>
                    <input type="text" class="form-control" value="<?= $clientAccount->country->name ?>" readonly="readonly" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label">Валюта</label>
                    <input type="text" class="form-control" value="<?= $clientAccount->currency ?>" readonly="readonly" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'trunk_id')
                    ->dropDownList($trunks, ['class' => 'select2'])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'actual_from')
                    ->widget(DateControl::class)
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'description')->textInput() ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-4">
                <div class="col-sm-6">
                    <?= $form->field($model, 'orig_enabled')->checkbox()->label('') ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'term_enabled')->checkbox()->label('') ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view']) . '";',
                        ]) .
                        Html::button('Подключить', ['class' => 'btn btn-primary', 'onClick' => "submitForm('add')"]),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>

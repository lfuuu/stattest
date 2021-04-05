<?php

/**
 * Создание/редактирование DID-группы
 *
 * @var \app\classes\BaseView $this
 * @var DidGroupForm $formModel
 * @var DidGroupPriceLevel $didGroupPriceLevelModel
 */

use app\classes\Html;
use app\forms\tariff\DidGroupForm;
use app\forms\tariff\DidGroupPriceLevelFormEdit;
use app\models\DidGroupPriceLevel;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\modules\nnp\models\NdcType;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$didGroup = $formModel->didGroup;

if (!$didGroup->isNewRecord) {
    $this->title = $didGroup->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Тарифы',
        ['label' => 'DID группы', 'url' => $cancelUrl = '/tariff/did-group'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();

    $this->registerJsVariable('formId', $form->getId());

    $viewParams = [
        'formModel' => $formModel,
        'didGroupPriceLevelModel' => $didGroupPriceLevelModel,
        'form' => $form,
    ];

    ?>

    <?= Html::hiddenInput('isFake', '0', ['id' => 'isFake']) ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <?php // Страна 
        ?>
        <div class="col-sm-<?= (NdcType::isCityDependent($didGroup->ndc_type_id) && !$didGroup->is_service ? 3 : 6) ?>">
            <?= $form->field($didGroup, 'country_code')
                ->widget(Select2::class, [
                    'data' => Country::getList($isWithEmpty = false),
                    'options' => [
                        'class' => 'formReload'
                    ],
                ]) ?>
        </div>

        <?php if (NdcType::isCityDependent($didGroup->ndc_type_id) && !$didGroup->is_service) : ?>
            <?php // Город 
            ?>
            <div class="col-sm-3">
                <?= $form->field($didGroup, 'city_id')
                    ->widget(Select2::class, [
                        'data' => City::getList($isWithEmpty = true, $didGroup->country_code),
                    ]) ?>
            </div>
        <?php endif; ?>

        <?php // Красивость 
        ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'beauty_level')
                ->widget(Select2::class, [
                    'data' => DidGroup::dao()->getBeautyLevelList($didGroup->isNewRecord),
                ]) ?>
        </div>

        <?php // Тип номера 
        ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'ndc_type_id')
                ->widget(Select2::class, [
                    'data' => NdcType::getList($isWithEmpty = true),
                    'options' => [
                        'class' => 'formReload'
                    ],
                ]) ?>
        </div>

    </div>

    <div class="row">

        <?php // Название 
        ?>
        <div class="col-sm-6">
            <?= $form->field($didGroup, 'name')->textInput() ?>
        </div>

        <?php // Комментарий 
        ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'comment') ?>
        </div>

        <?php // Служебная группа? 
        ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'is_service')->checkbox([
                'class' => 'formReload'
            ]) ?>
        </div>

    </div>

    <?= $this->render('_editDidGroupPriceLevel', $viewParams) ?>

    <?php // кнопки 
    ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($didGroup->isNewRecord ? 'Create' : 'Save'), $viewParams) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
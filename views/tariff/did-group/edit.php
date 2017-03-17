<?php
/**
 * Создание/редактирование DID-группы
 *
 * @var \app\classes\BaseView $this
 * @var DidGroupForm $formModel
 */

use app\classes\Html;
use app\forms\tariff\DidGroupForm;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\NumberType;
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

        <?php // Страна ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'country_code')
                ->widget(Select2::className(), [
                    'data' => Country::getList($isWithEmpty = false),
                    'options' => [
                        'class' => 'formReload'
                    ],
                ]) ?>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'city_id')
                ->widget(Select2::className(), [
                    'data' => City::getList($isWithEmpty = true, $didGroup->country_code),
                ]) ?>
        </div>

        <?php // Красивость ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'beauty_level')
                ->widget(Select2::className(), [
                    'data' => DidGroup::dao()->getBeautyLevelList($didGroup->isNewRecord),
                ]) ?>
        </div>

        <?php // Тип номера ?>
        <div class="col-sm-3">
            <?= $form->field($didGroup, 'number_type_id')
                ->widget(Select2::className(), [
                    'data' => NumberType::getList($isWithEmpty = true),
                ]) ?>
        </div>

    </div>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($didGroup, 'name')->textInput() ?>
        </div>

        <?php // Цена 1 ?>
        <div class="col-sm-2">
            <?= $form->field($didGroup, 'price1')->input('number', ['step' => 0.01]) ?>
        </div>

        <?php // Цена 2 ?>
        <div class="col-sm-2">
            <?= $form->field($didGroup, 'price2')->input('number', ['step' => 0.01]) ?>
        </div>

        <?php // Цена 3 ?>
        <div class="col-sm-2">
            <?= $form->field($didGroup, 'price3')->input('number', ['step' => 0.01]) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($didGroup->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

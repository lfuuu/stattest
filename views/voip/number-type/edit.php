<?php
/**
 * Создание/редактирование типа номера
 *
 * @var \yii\web\View $this
 * @var NumberTypeForm $formModel
 */

use app\classes\Html;
use app\classes\voip\forms\NumberTypeForm;
use app\models\Country;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$numberType = $formModel->numberType;

if (!$numberType->isNewRecord) {
    $this->title = $numberType->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => 'Тип номера', 'url' => $cancelUrl = '/voip/number-type/'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <?php // название ?>
        <div class="col-sm-4">
            <?= $form->field($numberType, 'name')->textInput() ?>
        </div>

        <?php // страны ?>
        <div class="col-sm-4">
            <label>Страны</label>
            <?= Select2::widget([
                'name' => 'NumberTypeCountry[]',
                'value' => array_keys((array)$numberType->numberTypeCountries),
                'data' => Country::getList(false),
                'options' => [
                    'multiple' => true,
                ],
            ]) ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($numberType->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$numberType->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

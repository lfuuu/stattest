<?php
/**
 * Создание/редактирование страны
 *
 * @var \yii\web\View $this
 * @var CountryForm $formModel
 */

use app\classes\dictionary\forms\CountryForm;
use app\classes\Html;
use app\classes\traits\YesNoTraits;
use app\models\Currency;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$country = $formModel->country;

if (!$country->isNewRecord) {
    $this->title = $country->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Страны', 'url' => '/dictionary/country/'],
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
        echo $this->render('//layouts/_alert', ['type' => 'danger', 'message' => $formModel->validateErrors]);
    }
    ?>

    <?php if ($country->isNewRecord) : ?>
        <div class="row">

            <?php // id ?>
            <div class="col-sm-4">
                <?= $form->field($country, 'code')->textInput(['type' => 'number']) ?>
            </div>

        </div>

    <?php endif ?>

    <div class="row">

        <?php // сокращение ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'alpha_3')->textInput() ?>
        </div>

        <?php // название ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'name')->textInput() ?>
        </div>

        <?php // префикс ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'prefix')->textInput(['type' => 'number']) ?>
        </div>

    </div>

    <div class="row">

        <?php // язык ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'lang')->textInput() ?>
        </div>

        <?php // валюта ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'currency_id')->widget(Select2::className(), [
                'data' => Currency::getList($country->isNewRecord),
            ]) ?>
        </div>

        <?php // вкл ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'in_use')->widget(Select2::className(), [
                'data' => YesNoTraits::getYesNoList(false),
            ]) ?>

        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">

        <?= Html::submitButton(Yii::t('common', $country->isNewRecord ? 'Create' : 'Save'), ['class' => 'btn btn-primary']) ?>

        <?php if (!$country->isNewRecord) : ?>
            <?= Html::submitButton(Yii::t('common', 'Drop'), [
                'name' => 'dropButton',
                'value' => 1,
                'class' => 'btn btn-danger pull-right',
                'onclick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
            ]) ?>
        <?php endif ?>

    </div>

    <?php ActiveForm::end(); ?>
</div>

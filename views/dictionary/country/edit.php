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
use yii\helpers\Url;
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
        ['label' => 'Страны', 'url' => $cancelUrl = Url::toRoute(['dictionary/country', $country->formName() . 'Filter[in_use]' => 1])],
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

    <?php if ($country->isNewRecord) : ?>
        <div class="row">

            <?php // id ?>
            <div class="col-sm-4">
                <?= $form->field($country, 'code')->textInput(['type' => 'number']) ?>
            </div>

            <?php // название ?>
            <div class="col-sm-8">
                <?= $form->field($country, 'name')->textInput() ?>
            </div>

        </div>

    <?php endif ?>

    <div class="row">

        <?php // сокращение ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'alpha_3')->textInput() ?>
        </div>

        <?php // префикс ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'prefix')->textInput(['type' => 'number']) ?>
        </div>

        <?php // URL сайта ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'site')->textInput() ?>
        </div>

    </div>

    <div class="row">

        <?php // язык ?>
        <div class="col-sm-4">
            <?= $form->field($country, 'lang')->dropDownList(\app\models\Language::getList()) ?>
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

        <?= Html::submitButton(
            Html::tag('i', '', ['class' => 'glyphicon glyphicon-save']) . ' ' . Yii::t('common', $country->isNewRecord ? 'Create' : 'Save'),
            [
                'class' => 'btn btn-primary',
                'aria-hidden' => 'true',
            ]
        ) ?>

        <?= Html::a(
            Html::tag('i', '', ['class' => 'glyphicon glyphicon-level-up']) . ' ' . Yii::t('common', 'Cancel'),
            $cancelUrl,
            [
                'class' => 'btn btn-link btn-cancel',
                'aria-hidden' => 'true',
            ]
        ) ?>

        <?php if (!$country->isNewRecord) : ?>
            <?= Html::submitButton(
                Html::tag('i', '', ['class' => 'glyphicon glyphicon-trash']) . ' ' . Yii::t('common', 'Drop'),
                [
                    'name' => 'dropButton',
                    'value' => 1,
                    'class' => 'btn btn-danger pull-right',
                    'aria-hidden' => 'true',
                    'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
                ]
            ) ?>
        <?php endif ?>

    </div>

    <?php ActiveForm::end(); ?>
</div>

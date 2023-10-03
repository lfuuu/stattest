<?php
/**
 * Создание/редактирование страны
 *
 * @var \app\classes\BaseView $this
 * @var CountryForm $formModel
 */

use app\classes\dictionary\forms\CountryForm;
use app\classes\traits\YesNoTraits;
use app\models\Currency;
use app\models\Language;
use app\models\Region;
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
        'Словари',
        ['label' => 'Страны', 'url' => $cancelUrl = '/dictionary/country/'],
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

        </div>

    <?php endif ?>

    <div class="row">

        <?php // эндоним ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'name')->textInput() ?>
        </div>

        <?php // русское название ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'name_rus')->textInput() ?>
        </div>

        <?php // полное русское название ?>
        <div class="col-sm-6">
            <?= $form->field($country, 'name_rus_full')->textInput() ?>
        </div>

    </div>

    <div class="row">

        <?php // сокращение ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'alpha_3')->textInput() ?>
        </div>

        <?php // язык ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'lang')->widget(Select2::class, [
                'data' => Language::getList(),
            ]) ?>
        </div>

        <?php // валюта ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'currency_id')->widget(Select2::class, [
                'data' => Currency::getList($isWithEmpty = $country->isNewRecord),
            ]) ?>
        </div>

        <?php // вкл ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'in_use')->widget(Select2::class, [
                'data' => YesNoTraits::getYesNoList($isWithEmpty = false),
            ]) ?>
        </div>

        <?php // вкл ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'is_show_in_lk')->widget(Select2::class, [
                'data' => YesNoTraits::getYesNoList($isWithEmpty = false),
            ]) ?>
        </div>

        <?php // префикс ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'prefix')->textInput(['type' => 'number']) ?>
        </div>

    </div>

    <div class="row">

        <?php // Регион (точка подключения) по умолчанию ?>
        <div class="col-sm-6">
            <?= $form->field($country, 'default_connection_point_id')->widget(Select2::class, [
                'data' => Region::getList($isWithEmpty = $country->isNewRecord),
            ]) ?>
        </div>

        <?php // URL сайта ?>
        <div class="col-sm-6">
            <?= $form->field($country, 'site')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <div class="row">
            <div class="col-sm-3">
                <?= $this->render('//layouts/_showHistory', ['model' => $country, 'idField' => 'code']) ?>
            </div>
            <div class="col-sm-9 text-right">
                <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
                <?= $this->render('//layouts/_submitButton' . ($country->isNewRecord ? 'Create' : 'Save')) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

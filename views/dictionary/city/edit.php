<?php
/**
 * Создание/редактирование города
 *
 * @var \yii\web\View $this
 * @var CityForm $formModel
 */

use app\classes\dictionary\forms\CityForm;
use app\models\Country;
use app\models\CityBillingMethod;
use app\models\Region;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$city = $formModel->city;

if (!$city->isNewRecord) {
    $this->title = $city->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Города', 'url' => $cancelUrl = '/dictionary/city'],
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

        <?php // ID ?>
        <div class="col-sm-2">
            <?= $form->field($city, 'id')->textInput() ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($city, 'name')->textInput() ?>
        </div>

        <?php // Страна ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'country_id')
                ->widget(Select2::className(), [
                    'data' => Country::getList($isWithEmpty = $city->isNewRecord),
                ]) ?>
        </div>

        <?php // Точка подключения ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'connection_point_id')
                ->widget(Select2::className(), [
                    'data' => Region::getList($isWithEmpty = $city->isNewRecord),
                ]) ?>
        </div>

    </div>

    <div class="row">
        <?php // Формат номеров ?>
        <div class="col-sm-4">
            <?= $form->field($city, 'voip_number_format')->textInput() ?>
        </div>

        <?php // Метод биллингования ?>
        <div class="col-sm-2">
            <?= $form->field($city, 'billing_method_id')
                ->widget(Select2::className(), [
                    'data' => CityBillingMethod::getList($isWithEmpty = true),
                ]) ?>
        </div>

        <?php // Показывать в ЛК
        if ($city->in_use) : ?>
        <div class="col-sm-2">
            <br />
            <?= $form->field($city, 'is_show_in_lk')->checkbox() ?>
        </div>
        <?php endif; ?>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($city->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
/**
 * Создание/редактирование города
 *
 * @var \app\classes\BaseView $this
 * @var CityForm $formModel
 */

use app\classes\dictionary\forms\CityForm;
use app\models\City;
use app\models\CityBillingMethod;
use app\models\Country;
use app\models\Region;
use app\classes\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

$city = $formModel->city;

if (!$city->isNewRecord) {
    $this->title = $city->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>
<?= Html::formLabel('Редактирование города'); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
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

        <div class="col-sm-4" id="create-city-name">
            <div class="form-group field-city-name required has-success">
                <label class="control-label" for="city-name">Название</label>
                <select id="name-city" class="form-control select2" style tabindex="-1" aria-hidden="true"
                        aria-invalid="false">
                </select>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-4" id="hidden-city-name">
            <?= $form->field($city, 'name')->textInput() ?>
        </div>

        <?php // Страна ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'country_id')
                ->widget(Select2::class, [
                    'data' => Country::getList($isWithEmpty = $city->isNewRecord),
                ]) ?>
        </div>

        <?php // Регион (точка подключения) ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'connection_point_id')
                ->widget(Select2::class, [
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
                ->widget(Select2::class, [
                    'data' => CityBillingMethod::getList($isWithEmpty = true),
                ]) ?>
        </div>

        <?php // Длина постфикса ?>
        <div class="col-sm-2">
            <?= $form->field($city, 'postfix_length')
                ->textInput([
                    'type' => 'number',
                    'step' => 1,
                    'min' => 4,
                    'max' => 11
                ]) ?>
        </div>

        <?php // Показывать в ЛК
        if ($city->in_use) : ?>
            <div class="col-sm-2">
                <?= $form->field($city, 'is_show_in_lk')
                    ->dropDownList(City::dao()->getIsShowInLkList()) ?>
            </div>
        <?php endif; ?>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <div class="row">
            <div class="col-sm-3">
                <?= $this->render('//layouts/_showHistory', ['model' => $city]) ?>
            </div>
            <div class="col-sm-9 text-right">
                <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
                <?= $this->render('//layouts/_submitButton' . ($city->isNewRecord ? 'Create' : 'Save')) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">

    var country_id = $("#city-country_id option:selected").val();

    var city_id = $("#city-id").val();

    if (country_id) {
        $('#hidden-city-name').show();
        $('#create-city-name').hide();
    } else {
        $('#hidden-city-name').hide();
        $('#create-city-name').show();
        $("#name-city").attr("disabled", "disabled");
        $("#city-id").attr("readonly", "readonly");
    }

    $('#city-country_id').on('change',
        function (e) {

            var country_id = $(e.target).val();

            $.get('/dictionary/city/ajax-city-list', {country_id: country_id}).done(function (data) {
                $("#name-city").removeAttr("disabled").html(data).trigger('change');
            });
        });

    $('#name-city').on('change',
        function (e) {
            var city_id_selected = $(e.target).val();
            var city_name_selected = $('#name-city option:selected').text();
            $('#city-id').val(city_id_selected);
            $('#city-name').val(city_name_selected);
        });
</script>

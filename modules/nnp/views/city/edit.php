<?php
/**
 * Создание/редактирование города
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp\forms\city\Form;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\Region;
use kartik\select2\Select2;
use yii\helpers\Url;
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
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Города', 'url' => $cancelUrl = '/nnp/city/'],
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

    <div class="row">

        <?php // Страна ?>
        <div class="col-sm-2">
            <?= $form->field($city, 'country_code')->widget(Select2::class, [
                'data' => Country::getList($isWithEmpty = true),
            ]) ?>
            <div>
                <?= ($country = $city->country) ?
                    Html::a($country->name_rus, $country->getUrl()) :
                    '' ?>
            </div>
        </div>

        <div class="col-sm-2">
            <?= $form->field($city, 'region_id')->widget(Select2::class, [
                'data' => Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $city->country_code),
            ]) ?>
            <div>
                <?= ($region = $city->region) ?
                    Html::a($region->name, $region->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'name')->textInput() ?>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-3">
            <?= $form->field($city, 'name_translit')->textInput() ?>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $city->getAttributeLabel('cnt') ?></label>
            <div>
                <?= $city->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $city->country_code, 'NumberRangeFilter[city_id]' => $city->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $city->country_code, 'NumberFilter[city_id]' => $city->id])
                ) . ')' ?>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-2">
            <?php
            $cityList = City::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $region->country_code);

            if ($city->id) {
                unset($cityList[$city->id]);
            }

            ?>
            <?= $form->field($city, 'parent_id')->widget(Select2::class, [
                'data' => $cityList,
            ]) ?>
            <div>
                <?= ($cityParent = $city->parent) ?
                    Html::a($cityParent->name, $cityParent->getUrl()) :
                    '' ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($city->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php if (!$city->isNewRecord && $country && $region) : ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $this->render('//layouts/_submitButtonDrop') ?> &nbsp;, заменив на
            </div>
            <div class="col-sm-4">
                <?php
                $citiesList = City::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $country->code, $region->id, 0);
                unset($citiesList[$city->id]); // убрать себя
                ?>
                <?= Select2::widget([
                    'name' => 'newCityId',
                    'data' => $citiesList,
                ]) ?>
            </div>
        </div>
    <?php endif ?>

    <?php ActiveForm::end(); ?>
</div>

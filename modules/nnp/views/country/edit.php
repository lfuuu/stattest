<?php
/**
 * редактирование страны
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp\forms\country\Form;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Operator;
use kartik\select2\Select2;
use yii\db\ArrayExpression;
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
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Страны', 'url' => $cancelUrl = '/nnp/country/'],
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

        <?php if (false) { // Страна ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'country_code')->widget(Select2::class, [
                'data' => [],//Country::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $indexBy = 'code'),
            ]) ?>
            <div>
                <?= ($country = $country->country) ?
                    Html::a($country->name_rus, $country->getUrl()) :
                    '' ?>
            </div>
        </div>

        <div class="col-sm-2">
            <?= $form->field($country, 'parent_id')->widget(Select2::class, [
                'data' => [],//Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $country->country_code),
            ]) ?>
            <div>
                <?= ($countryParent = $country->parent) ?
                    Html::a($countryParent->name, $countryParent->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php } // Название ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'name')->textInput(['disabled' => !$country->isNewRecord]) ?>
        </div>

        <?php // Название на русском ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'name_rus')->textInput(['disabled' => !$country->isNewRecord]) ?>
        </div>

        <?php // Название на английском ?>
        <div class="col-sm-2">
            <?= $form->field($country, 'name_eng')->textInput(['disabled' => !$country->isNewRecord]) ?>
        </div>

        <?php // Префикс ?>
        <div class="col-sm-1">
            <?= $form->field($country, 'prefix')->textInput() ?>
        </div>

        <?php // Префикы ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'prefixes')->textInput(['value' => ($country->prefixes instanceof ArrayExpression)
                ? implode (', ', $country->prefixes->getValue())
                : (is_array($country->prefixes) ? implode (', ', $country->prefixes) : $country->prefixes)]) ?>
        </div>

        <?php // Открытый номерной план? ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'is_open_numbering_plan')->checkbox() ?>
        </div>

        <?php // использовать слабое соответствие? ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'use_weak_matching')->checkbox() ?>
        </div>

        <?php // Оператор по-умолчания ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'default_operator')->dropDownList(Operator::getList(true), ['class' => 'select2']) ?>
        </div>

        <?php // NDC тип по-умолчанию ?>
        <div class="col-sm-3">
            <?= $form->field($country, 'default_type_ndc')->dropDownList(NdcType::getList(true)) ?>
        </div>

        <?php // Код ?>
        <div class="col-sm-3">
            <?= $form->field($country,'code')->textInput() ?>
        </div>

        <?php // 2х-буквенный код ?>
        <div class="col-sm-3">
            <?= $form->field($country,'alpha_2')->textInput() ?>
        </div>

        <?php // 3х-буквенный код ?>
        <div class="col-sm-3">
            <?= $form->field($country,'alpha_3')->textInput() ?>
        </div>

        <?php // MCC ?>
        <div class="col-sm-3">
            <?= $form->field($country,'mcc')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($country->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

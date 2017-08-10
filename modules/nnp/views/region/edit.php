<?php
/**
 * Создание/редактирование регионы
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp\forms\region\Form;
use app\modules\nnp\models\Country;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$region = $formModel->region;

if (!$region->isNewRecord) {
    $this->title = $region->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Регионы', 'url' => $cancelUrl = '/nnp/region/'],
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
            <?= $form->field($region, 'country_code')->widget(Select2::className(), [
                'data' => Country::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $indexBy = 'code'),
            ]) ?>
            <div>
                <?= ($country = $region->country) ?
                    Html::a($country->name_rus, $country->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($region, 'name')->textInput() ?>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-4">
            <?= $form->field($region, 'name_translit')->textInput() ?>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $region->getAttributeLabel('cnt') ?></label>
            <div>
                <?= Html::a(
                    $region->cnt,
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $region->country_code, 'NumberRangeFilter[region_id]' => $region->id])
                ) ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($region->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

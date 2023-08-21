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
use app\modules\nnp\models\Region;
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
            <?= $form->field($region, 'country_code')->widget(Select2::class, [
                'data' => Country::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $indexBy = 'code'),
            ]) ?>
            <div>
                <?= ($country = $region->country) ?
                    Html::a($country->name_rus, $country->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-1">
            <?= $form->field($region, 'iso')->textInput() ?>
        </div>

        <div class="col-sm-2">
            <?php
                $regionList = Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $region->country_code);

                if ($region->id) {
                    unset($regionList[$region->id]);
                }

            ?>
            <?= $form->field($region, 'parent_id')->widget(Select2::class, [
                'data' => $regionList,
            ]) ?>
            <div>
                <?= ($regionParent = $region->parent) ?
                    Html::a($regionParent->name, $regionParent->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-2">
            <?= $form->field($region, 'name')->textInput() ?>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-2">
            <?= $form->field($region, 'name_translit')->textInput() ?>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $region->getAttributeLabel('cnt') ?></label>
            <div>
                <?= $region->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $region->country_code, 'NumberRangeFilter[region_id]' => $region->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $region->country_code, 'NumberFilter[region_id]' => $region->id])
                ) . ')' ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($region->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php if (!$region->isNewRecord && $country) : ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $this->render('//layouts/_submitButtonDrop') ?> &nbsp;, заменив на
            </div>
            <div class="col-sm-4">
                <?php
                $regionsList = Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $country->code, 0);
                unset($regionsList[$region->id]); // убрать себя
                ?>
                <?= Select2::widget([
                    'name' => 'newRegionId',
                    'data' => $regionsList,
                ]) ?>
            </div>
        </div>
    <?php endif ?>

    <?php ActiveForm::end(); ?>
</div>

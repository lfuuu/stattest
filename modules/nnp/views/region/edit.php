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
    $this->title = ['label' => $region->name, 'url' => $region->getUrl()];
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Регионы', 'url' => $cancelUrl = '/nnp/region/'],
        $region->country_code ? ['label' => $region->country,  'url' => $cancelUrl = ['/nnp/region/', 'RegionFilter' => ['country_code' => $formModel->region->country_code]]] : ['label' => 'Добавление региона'],
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

        <?php // if valid  ?>
        <?php $childs = $region->childs; ?>
        <div class="col-sm-3">
            <br />
            <?= $form->field($region, 'is_valid')->checkbox($childs ? ['disabled' => true] : []) . ($childs ? $form->field($region, 'is_valid')->hiddenInput()->label('') : '')?>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-2">
            <?php
            $regionList = Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $region->country_code, null, true);

            if ($region->id) {
                unset($regionList[$region->id]);
            }

            ?>
            <?= $form->field($region, 'parent_id')->widget(Select2::class, [
                'data' => $regionList,
            ]) ?>
            <div>
            </div>
        </div>
        <div class="col-sm-2">
            <?php
            if ($parent = $region->parent) {
                echo Html::a(
                    'перейти к родителю',
                    Url::to($parent->getUrl())
                );
            }

            if ($childs) {
                echo 'Синонимы: <br />';

                $i = 0;
                foreach ($childs as $child) {
                    echo ++$i . '. ' . Html::a(
                            strval($child),
                            Url::to($child->getUrl())
                        ) . '<br />';
                }
            }

            ?>
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

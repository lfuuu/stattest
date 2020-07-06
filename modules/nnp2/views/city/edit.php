<?php
/**
 * Создание/редактирование города
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp2\forms\city\Form;
use app\modules\nnp2\models\City;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$model = $formModel->city;

if (!$model->isNewRecord) {
    $this->title = $model->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Города', 'url' => $cancelUrl = '/nnp2/city/'],
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
            <label><?= $model->getAttributeLabel('country_code') ?></label>
            <div><?= $model->country ? $model->country->name_rus : '' ?></div>
        </div>

        <?php // Регион ?>
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('region_id') ?></label>
            <div><?= $model->region ? $model->region->name : '' ?></div>
        </div>

        <?php // Название ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('name') ?></label>
            <div><?= $model->name ?></div>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('name_translit') ?></label>
            <div><?= $model->name_translit ?></div>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('cnt') ?></label>
            <div>
                <?= $model->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp2/number-range/', 'NumberRangeFilter[country_code]' => $model->country_code, 'NumberRangeFilter[city_id]' => $model->id])
                )
                . ')' ?>
            </div>
        </div>
    </div>

    <div class="row">
        <br />
    </div>

    <div class="row">
        <?php // Родитель ?>
        <div class="col-sm-3">
            <?php
            $citiesList = City::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $model->country_code, $model->region->id, true);
            if ($model->id) {
                unset($citiesList[$model->id]); // убрать себя
            }
            ?>
            <?= $form->field($model, 'parent_id')->widget(Select2::class, [
                'data' => $citiesList,
            ]) ?>
            <?php
                if ($parent = $model->parent) {
                    echo Html::a(
                        'перейти к родителю',
                        Url::to($parent->getUrl())
                    );
                }

                if ($childs = $model->childs) {
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

        <?php // if valid  ?>
        <div class="col-sm-3">
            <br />
            <?= $form->field($model, 'is_valid')->checkbox() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($model->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
/**
 * Создание/редактирование регионы
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp2\forms\region\Form;
use app\modules\nnp2\models\Region;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$model = $formModel->region;

if (!$model->isNewRecord) {
    $this->title = $model->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Регионы', 'url' => $cancelUrl = '/nnp2/region/'],
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

        <?php // ISO ?>
        <div class="col-sm-1">
            <label><?= $model->getAttributeLabel('iso') ?></label>
            <div><?= $model->iso ? : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Название ?>
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('name') ?></label>
            <div><?= $model->name ? : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('name_translit') ?></label>
            <div><?= $model->name_translit ? : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('cnt') ?></label>
            <div>
                <?= $model->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp2/number-range/', 'NumberRangeFilter[country_code]' => $model->country_code, 'NumberRangeFilter[region_id]' => $model->id])
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
        <div class="col-sm-2">
            <?php
            $regionsList = Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $model->country_code, true, 0);
            if ($model->id) {
                unset($regionsList[$model->id]); // убрать себя
            }
            ?>
            <?= $form->field($model, 'parent_id')->widget(Select2::class, [
                'data' => $regionsList,
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

<?php
/**
 * Создание/редактирование местоположения
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp2\models\Region;
use yii\helpers\Url;
use app\modules\nnp2\forms\geoPlace\Form;
use app\modules\nnp2\models\GeoPlace;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$model = $formModel->geoPlace;

if (!$model->isNewRecord) {
    $this->title = strval($model);
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Местоположения', 'url' => $cancelUrl = '/nnp2/geo-place/'],
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

        <?php // Название ?>
        <div class="col-sm-6 bold">
            <?= strval($model) ?>
            <br /><br /><br />
        </div>

        <?php // Регион ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('region_id') ?></label>
            <div>
                <?
                    if (!$model->region || $model->region->is_valid) {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                    } else {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                    }
                ?>
                <?= $model->region ? Html::a($model->region->name, Region::getUrlById($model->region->id)) : Yii::t('common', '(not set)') ?>
            </div>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('city_id') ?></label>
            <div>
                <?
                    if (!$model->city || $model->city->is_valid) {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                    } else {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                    }
                ?>
                <?= $model->city ? Html::a($model->city->name, Region::getUrlById($model->city->id)) : Yii::t('common', '(not set)') ?>
            </div>
        </div>

    </div>

    <div class="row">
        <?php // Родитель ?>
        <div class="col-sm-3">
            <?php
            $geoPlacesList = GeoPlace::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $model->country_code, $model->ndc, $model->region_id);
            if ($model->id) {
                unset($geoPlacesList[$model->id]); // убрать себя
            }
            ?>
            <?= $form->field($model, 'parent_id')->widget(Select2::class, [
                'data' => $geoPlacesList,
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

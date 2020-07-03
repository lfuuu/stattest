<?php
/**
 * Создание/редактирование типа NDC
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use yii\helpers\Url;
use app\modules\nnp2\forms\ndcType\Form;
use app\modules\nnp2\models\NdcType;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$model = $formModel->ndcType;

if (!$model->isNewRecord) {
    $this->title = $model->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Типы NDC', 'url' => $cancelUrl = '/nnp2/ndc-type/'],
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
        <div class="col-sm-2">
            <label><?= $model->getAttributeLabel('name') ?></label>
            <div><?= $model->name ? : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // зависимость от города ?>
        <div class="col-sm-6">
            <label><?= $model->getAttributeLabel('is_city_dependent') ?></label>
            <div><?= $model->is_city_dependent ? Yii::t('common', 'Yes') : Yii::t('common', 'No') ?></div>
        </div>
    </div>

    <div class="row">
        <br />
    </div>

    <div class="row">
        <?php // Родитель ?>
        <div class="col-sm-2">
            <?php
                $ndcTypeList = NdcType::getList($isWithEmpty = true);
                if ($model->id) {
                    unset($ndcTypeList[$model->id]); // убрать себя
                }
            ?>
            <?= $form->field($model, 'parent_id')->widget(Select2::class, [
                'data' => $ndcTypeList,
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
        <div class="col-sm-6">
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

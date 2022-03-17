<?php
/**
 * Создание/редактирование оператора
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp\forms\operator\Form;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\Operator;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$operator = $formModel->operator;

if (!$operator->isNewRecord) {
    $this->title = $operator->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Операторы', 'url' => $cancelUrl = '/nnp/operator/'],
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
            <?= $form->field($operator, 'country_code')->widget(Select2::class, [
                'data' => Country::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $indexBy = 'code'),
            ]) ?>
            <div>
                <?= ($country = $operator->country) ?
                    Html::a($country->name_rus, $country->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($operator, 'name')->textInput() ?>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-3">
            <?= $form->field($operator, 'name_translit')->textInput() ?>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-3">
            <label><?= $operator->getAttributeLabel('cnt') ?></label>
            <div>
                <?= $operator->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $operator->country_code, 'NumberRangeFilter[operator_id]' => $operator->id])
                ) . ', ' .
                Html::a(
                    'портированные',
                    Url::to(['/nnp/number/', 'NumberFilter[country_code]' => $operator->country_code, 'NumberFilter[operator_id]' => $operator->id])
                ) . ')' ?>
            </div>
        </div>

    </div>

    <div class="row">
        <?php // Группа оператора ?>
        <div class="col-sm-3">
            <?= $form->field($operator, 'group')->widget(Select2::className(), [
                'data' => ['' => '- Все -'] + Operator::$groups,
            ]) ?>
        </div>

        <?php // partner code ?>
        <div class="col-sm-2">
            <?= $form->field($operator, 'partner_code')->textInput() ?>
        </div>


        <?php // operator src code ?>
        <div class="col-sm-2">
            <?= $form->field($operator, 'operator_src_code')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($operator->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php if (!$operator->isNewRecord && $country) : ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $this->render('//layouts/_submitButtonDrop') ?> &nbsp;, заменив на
            </div>
            <div class="col-sm-4">
				<?php
                $operatorsList = Operator::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $country->code, 0);
                unset($operatorsList[$operator->id]); // убрать себя
				?>
                <?= Select2::widget([
                    'name' => 'newOperatorId',
                    'data' => $operatorsList,
                ]) ?>
            </div>
        </div>
    <?php endif ?>

    <?php ActiveForm::end(); ?>
</div>

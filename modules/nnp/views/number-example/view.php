<?php
/**
 * Просмотр примера номеров
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\numberExample\Form;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$numberExample = $formModel->numberExample;
$this->title = $numberExample->ndc . ' ' . $numberExample->full_number;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Примеры номеров', 'url' => $cancelUrl = '/nnp/number-example/'],
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

        <div class="col-sm-3">
            <label>Пример номера</label>
            <div>
                <?= $numberExample->full_number ?>
            </div>
        </div>

        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('prefix') ?></label>
            <div><?= $numberExample->prefix ?></div>
        </div>

        <?php // Страна ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('country_code') ?></label>
            <div><?= $numberExample->country ? $numberExample->country->name_rus : '' ?></div>
        </div>

        <?php // NDC ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('ndc') ?></label>
            <div><?= $numberExample->ndc ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('ranges') ?></label>
            <div><?= $numberExample->ranges ?></div>
        </div>

        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('numbers') ?></label>
            <div><?= $numberExample->numbers ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Регион ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('region_id') ?></label>
            <div><?= $numberExample->region ? $numberExample->region->name : '' ?></div>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('city_id') ?></label>
            <div><?= $numberExample->city ? $numberExample->city->name : '' ?></div>
        </div>

        <?php // Оператор ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('operator_id') ?></label>
            <div><?= $numberExample->operator ? $numberExample->operator->name : '' ?></div>
        </div>

        <?php // Тип NDC ?>
        <div class="col-sm-3">
            <label><?= $numberExample->getAttributeLabel('ndc_type_id') ?></label>
            <div>
                <?= $numberExample->ndcType ? $numberExample->ndcType->name : '' ?>
                <?= $numberExample->ndc_type_id ? ' ('. $numberExample->ndc_type_id .')' : '' ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

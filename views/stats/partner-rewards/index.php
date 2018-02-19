<?php
/**
 * Вознаграждения партнеров
 *
 * @var \app\classes\BaseView $this
 * @var PartnerRewardsFilter $filterModel
 */

use app\classes\Html;
use app\models\filter\PartnerRewardsFilter;
use app\widgets\MonthPicker;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Html::formLabel($this->title = 'Вознаграждения партнеров');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => $this->title, 'url' => $baseUrl = Url::toRoute('stats/partner-rewards')]
    ],
]);
?>

<div class="row">
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => $baseUrl]) ?>

    <div class="col-sm-1 text-right">
        Месяц:
    </div>
    <div class="col-sm-2">
        <?= MonthPicker::widget([
            'name' => 'filter[month]',
            'value' => $filterModel->month,
            'options' => [
                'class' => 'form-control input-sm',
            ],
        ]) ?>
    </div>

    <div class="col-sm-1 text-right">
        Партнер:
    </div>
    <div class="col-sm-3">
        <?= Select2::widget([
            'name' => 'filter[partner_contract_id]',
            'data' => $filterModel->partnersList,
            'value' => $filterModel->partner_contract_id,
            'options' => [
                'placeholder' => '-- Выбрать партнера --',
            ],
        ]);
        ?>
    </div>

    <div class="col-sm-2">
        <?= Html::submitButton('Сформировать', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
if ($filterModel->partner_contract_id) {
    echo $this->render(($filterModel->isExtendsMode ? '_grid_extends' : '_grid'), [
        'filterModel' => $filterModel,
    ]);
}
?>

<?= $this->render('incorrect-data', ['filterModel' => $filterModel]) ?>


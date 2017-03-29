<?php
/**
 * Счет-фактура
 *
 * @var \app\classes\BaseView $this
 * @var string $date
 * @var array $bills
 * @var string $langCode
 */

use app\classes\uu\model\AccountEntry;
use app\forms\templates\uu\InvoiceForm;
use app\widgets\MonthPicker;
use kartik\tabs\TabsX;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Invoice'),
    ],
]);

$attributeLabels = (new AccountEntry)->attributeLabels();
?>

<div class="row">
    <?php
    $form = ActiveForm::begin([
        'method' => 'get',
        'action' => '/uu/invoice/view',
    ])
    ?>
        <div class="col-sm-2">
            <?= MonthPicker::widget([
                'name' => 'month',
                'value' => $month = substr($date, 0, 7), // гггг-мм
                'options' => [
                    'class' => 'form-control input-sm',
                    'onChange' => '$(this).parents("form").submit()',
                ],
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= Select2::widget([
                'name' => 'langCode',
                'value' => $langCode,
                'data' => \app\models\Language::getList($isWithEmpty = true),
                'options' => [
                    'onChange' => '$(this).parents("form").submit()',
                ],
            ]) ?>
        </div>
    <?php ActiveForm::end(); ?>

    <div class="col-sm-8">
        <div class="pull-right">
            <?php

            if ($langCode === InvoiceForm::UNIVERSAL_INVOICE_KEY) {
                echo $this->render('//layouts/_link', [
                    'text' => 'Стандартная счет-фактура',
                    'url' => Url::toRoute([
                        '/uu/invoice/view',
                        'month' => $month,
                    ]),
                    'params' => [
                        'class' => 'btn btn-warning',
                    ],
                ]);
            } else {
                echo $this->render('//layouts/_link', [
                    'text' => 'Универсальная счет-фактура',
                    'url' => Url::toRoute([
                        '/uu/invoice/view',
                        'month' => $month,
                        'langCode' => InvoiceForm::UNIVERSAL_INVOICE_KEY,
                    ]),
                    'params' => [
                        'class' => 'btn btn-warning',
                    ],
                ]);
            }
            ?>
        </div>
    </div>
</div>
<br />

<?php

$billTabs = [];
foreach ($bills as $bill) {
    $billTabs[] = [
        'label' => $month . ' #' . $bill['id'],
        'content' => $this->render('_billTab', [
            'billId' => $bill['id'],
            'langCode' => $langCode,
            'month' => $month,
        ]),
        'headerOptions' => [],
        'options' => ['style' => 'white-space: nowrap;'],
    ];
}

echo TabsX::widget([
    'id' => 'tabs-uu-invoice',
    'items' => $billTabs,
    'position' => TabsX::POS_ABOVE,
    'bordered' => true,
    'encodeLabels' => false,
]);
<?php
/**
 * Счет-фактура
 *
 * @var \yii\web\View $this
 * @var string $date
 * @var [] $invoice
 * @var string $invoiceContent
 * @var string $langCode
 */

use app\classes\uu\model\AccountEntry;
use app\widgets\MonthPicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\forms\templates\uu\InvoiceForm;

echo Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Invoice'),
    ],
]);

$attributeLabels = (new AccountEntry)->attributeLabels();
?>

    <div class="row">
        <div class="col-sm-2">

            <?php
            $form = ActiveForm::begin([
                'method' => 'get',
                'action' => '/uu/invoice/view',
            ])
            ?>
            <?= MonthPicker::widget([
                'name' => 'month',
                'value' => $month = substr($date, 0, 7), // гггг-мм
                'options' => [
                    'class' => 'form-control input-sm',
                    'onChange' => '$(this).parent("form").submit()',
                ],
            ]) ?>
            <?php ActiveForm::end(); ?>

        </div>
        <div class="col-sm-10">
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
                            'langCode' => InvoiceForm::UNIVERSAL_INVOICE_KEY
                        ]),
                        'params' => [
                            'class' => 'btn btn-warning',
                        ],
                    ]);
                }
                ?>

                <?= $this->render('//layouts/_link', [
                    'text' => 'Печать',
                    'url' => Url::toRoute(['/uu/invoice/view', 'renderMode' => 'print', 'month' => $month, 'langCode' => $langCode]),
                    'glyphicon' => 'glyphicon glyphicon-print',
                    'params' => [
                        'class' => 'btn btn-primary',
                        'target' => '_blank',
                    ],
                ]) ?>

                <?= $this->render('//layouts/_link', [
                    'text' => 'Скачать в PDF',
                    'url' => Url::toRoute(['/uu/invoice/view', 'renderMode' => 'pdf', 'month' => $month, 'langCode' => $langCode]),
                    'glyphicon' => 'glyphicon glyphicon-download-alt',
                    'params' => [
                        'class' => 'btn btn-success',
                    ],
                ]) ?>

                <?= $this->render('//layouts/_link', [
                    'text' => 'Скачать в Word',
                    'url' => Url::toRoute(['/uu/invoice/view', 'renderMode' => 'mhtml', 'month' => $month, 'langCode' => $langCode]),
                    'glyphicon' => 'glyphicon glyphicon-download-alt',
                    'params' => [
                        'class' => 'btn btn-success',
                    ],
                ]) ?>

            </div>
        </div>
    </div>
    <br/>

<?= $invoiceContent ?>
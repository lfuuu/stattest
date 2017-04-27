<?php

use yii\helpers\Url;
use app\classes\Html;
use kartik\tabs\TabsX;
use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/**
 * @var \app\modules\notifier\forms\SchemesForm $dataForm
 */

echo Html::formLabel('Общие схемы оповещения');

echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Общие схемы оповещения', 'url' => Url::toRoute(['/notifier/schemes'])],
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin();

    $tabs = [];

    foreach ($dataForm->getAvailableCountries() as $countryCode => $countryName) {
        $tabs[] = [
            'label' => $countryName,
            'content' => $this->render('grid',
                [
                    'dataForm' => $dataForm,
                    'countryCode' => $countryCode,
                ]
            ),
        ];
    }

    echo TabsX::widget([
        'items' => $tabs,
        'position' => TabsX::POS_ABOVE,
        'bordered' => true,
        'encodeLabels' => false,
    ]);

    ActiveForm::end();
    ?>
</div>

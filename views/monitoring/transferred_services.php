<?php

use app\classes\Html;
use app\helpers\monitoring\TransferredRegularServiceDecorator;
use app\helpers\monitoring\TransferredUniversalServiceDecorator;
use app\models\ClientAccount;
use kartik\tabs\TabsX;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var ClientAccount|null $clientAccount */
/** @var array $regularServices */
/** @var array $universalServices */

$clientName = !is_null($clientAccount) ?
    ' для клиента ' . $clientAccount->contract->contragent->name :
    '';

echo Html::formLabel('Перемещаемые услуги' . $clientName);

echo Breadcrumbs::widget([
    'links' => [
        'Мониторинг',
        ['label' => 'Перемещаемые услуги' . $clientName, 'url' => $baseUrl = '/monitoring/transferred-services'],
    ],
]);
?>

<div class="text-right">
    <?= $this->render('//layouts/_link', [
        'text' => ($clientName ? 'Показать для всех клиентов' : 'Показать для текущего клиента'),
        'glyphicon' => 'glyphicon-filter',
        'url' => Url::toRoute([$baseUrl, 'isCurrentOnly' => ($clientName ? 0 : 1)]),
        'params' => [
            'class' => 'btn btn-primary',
        ],
    ]) ?>
</div>

<?php
echo TabsX::widget([
    'id' => 'tabs-transferred-services',
    'items' => [
        [
            'label' => 'Не универсальные услуги',
            'content' => $this->render('transferred_services_table', [
                'services' => array_map(function ($service) {
                    return new TransferredRegularServiceDecorator(['service' => $service]);
                }, $regularServices),
            ]),
            'headerOptions' => [],
            'options' => ['style' => 'white-space: nowrap;'],
        ],
        [
            'label' => 'Универсальные услуги',
            'content' => $this->render('transferred_services_table', [
                'services' => array_map(function ($service) {
                    return new TransferredUniversalServiceDecorator(['service' => $service]);
                }, $universalServices),
            ]),
            'headerOptions' => [],
            'options' => ['style' => 'white-space: nowrap;'],
        ],
    ],
    'containerOptions' => [
        'class' => 'col-sm-12',
    ],
    'position' => TabsX::POS_ABOVE,
    'bordered' => false,
    'encodeLabels' => false,
]);
?>
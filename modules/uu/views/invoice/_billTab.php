<?php

/**
 * @var int $billId
 * @var string $langCode
 * @var string $month
 */

use app\classes\Html;
use yii\helpers\Url;

$urlData = [
    '/uu/invoice/get',
    'billId' => $billId,
    'langCode' => ($langCode ?: '')
];
$frameName = $billId . '.' . $langCode;
?>

    <div class="pull-right">
        <?= $this->render('//layouts/_link', [
            'text' => 'Печать',
            'url' => Url::toRoute($urlData),
            'glyphicon' => 'glyphicon glyphicon-print',
            'params' => [
                'class' => 'btn btn-primary',
                'target' => '_blank',
            ],
        ]) ?>

        <?= $this->render('//layouts/_link', [
            'text' => 'Посмотерть в PDF',
            'url' => Url::toRoute(['renderMode' => 'pdf', 'isShow' => 1] + $urlData),
            'glyphicon' => 'glyphicon glyphicon-download-alt',
            'params' => [
                'target' => '_blank',
                'class' => 'btn btn-success',
            ],
        ]) ?>

        <?= $this->render('//layouts/_link', [
            'text' => 'Скачать в PDF',
            'url' => Url::toRoute(['renderMode' => 'pdf'] + $urlData),
            'glyphicon' => 'glyphicon glyphicon-download-alt',
            'params' => [
                'target' => '_blank',
                'class' => 'btn btn-success',
            ],
        ]) ?>

        <?= $this->render('//layouts/_link', [
            'text' => 'Скачать в Word',
            'url' => Url::toRoute(['renderMode' => 'mhtml'] + $urlData),
            'glyphicon' => 'glyphicon glyphicon-download-alt',
            'params' => [
                'target' => '_blank',
                'class' => 'btn btn-success',
            ],
        ]) ?>

    </div>

    <div class="clearfix"></div>
    <br/>

<?= Html::tag('iframe', '', [
    'src' => Url::toRoute($urlData),
    'style' => 'width: 100%; height: 780px;',
    'frameborder' => 0,
    'name' => $frameName,
]);

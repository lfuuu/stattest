<?php

use app\classes\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */

echo \yii\widgets\Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal services') .
                $this->render('//layouts/_helpConfluence', \app\modules\uu\models\AccountTariff::getHelpConfluence()),
            'encode' => false,
        ],

        [
            'label' => $this->title = $serviceType ? $serviceType->name : 'Телефония',
            'url' => \yii\helpers\Url::to(['/uu/account-tariff', 'serviceTypeId' => \app\modules\uu\models\ServiceType::ID_VOIP])
        ],
        [
            'label' => 'Массовое отключение номеров',
            'url' => \yii\helpers\Url::to(['/uu/account-tariff/disable']),
        ],
    ],
]);

echo Html::tag('b', 'Введите список номеров') . '<br>';

$form = ActiveForm::begin(['method' => 'POST']);

echo Html::textarea('numbers', '', ['class' => 'form-control', 'style' => 'width: 50%; height: 100px;']) . '<br>';

echo Html::submitButton('Отправить', ['class' => 'btn btn-success']);

ActiveForm::end();

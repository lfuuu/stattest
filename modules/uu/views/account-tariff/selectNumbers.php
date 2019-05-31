<?php

/* @var $this \yii\web\View */

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $info array */
?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal services') .
                $this->render('//layouts/_helpConfluence', AccountTariff::getHelpConfluence()),
            'encode' => false,
        ],

        [
            'label' => $this->title = $serviceType ? $serviceType->name : 'Телефония',
            'url' => \yii\helpers\Url::to(['/uu/account-tariff', 'serviceTypeId' => ServiceType::ID_VOIP])
        ],
        [
            'label' => 'Массовое отключение номеров',
            'url' => \yii\helpers\Url::to(['/uu/account-tariff/disable']),
        ],
    ],
]); ?>


<?php $form = ActiveForm::begin(['action' => Url::to(['close-numbers']), 'method' => 'POST']); ?>

    <label style="margin: 10px"><input type="checkbox" onclick="$('.numbers-checkbox').not('*[data-disabled]').click()"> Выбрать все </label>


<div class="alert alert-danger row">
    <?php foreach ($info as $number => $message): ?>
        <div class="col-sm-3">
            <label>
                <input class="numbers-checkbox" name="numbers[]" value=<?=$number?> type="checkbox" <?= (($message !== 'OK') ? 'disabled data-disabled' : '') ?>>
                <?= $number . (($message !== 'OK') ? ' (' . $message . ')' : '') ?>
            </label>
        </div>
    <?php endforeach; ?>
</div>
    <div class="row">
        <button type="submit" class="btn btn-success pull-left" style="margin-right: 10px">Отправить</button>
        <?= DatePicker::widget(
            [
                'name' => 'date',
                'value' => date(DateTimeZoneHelper::DATE_FORMAT),
                'removeButton' => false,
                'options' => ['class' => 'form-control'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]
        ); ?>
    </div>
<?php ActiveForm::end(); ?>

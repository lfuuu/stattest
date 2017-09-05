<?php
/**
 * Создание/редактирование универсальной услуги. Лог тарифов
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

// app\classes\grid\GridView добавляет много лишнего
use app\modules\uu\models\AccountTariffLog;
use kartik\grid\GridView;
use yii\helpers\Html;

$accountTariff = $formModel->accountTariff;
?>

<div class="account-tariff-edit-log-form well">
    <?php
    // добавить тариф (только при редактировании)
    if (!$isReadOnly && !$accountTariff->isNewRecord && !$accountTariff->isLogCancelable()) {
        echo $this->render('_editLogForm', ['formModel' => $formModel]);
    }
    ?>

    <?= $this->render('//layouts/_toggleButton', ['divSelector' => '#account-tariff-log-grid', 'title' => 'Лог тарифов']) ?>

    <div id="account-tariff-log-grid" class="collapse">
        <?= GridView::widget([
            'dataProvider' => $formModel->getAccountTariffLogGrid(),
            'columns' => [

                [
                    'attribute' => 'tariff_period_id',
                    'format' => 'html',
                    'value' => function (AccountTariffLog $accountTariffLog) {
                        $accountTariff = $accountTariffLog->accountTariff;
                        return
                            ($accountTariffLog->tariffPeriod ?
                                Html::a(
                                    Html::encode($accountTariffLog->tariffPeriod->getName()),
                                    $accountTariffLog->tariffPeriod->getUrl()
                                ) :
                                Yii::t('common', 'Switched off')) .
                            ' ' .

                            (
                            strtotime($accountTariffLog->actual_from_utc) >= time() ?
                                Html::a(
                                    Html::tag('i', '', [
                                        'class' => 'glyphicon glyphicon-erase',
                                        'aria-hidden' => 'true',
                                    ]) . ' ' .
                                    Yii::t('common', 'Reject'),
                                    [
                                        '/uu/account-tariff/cancel',
                                        'id' => $accountTariff->id,
                                        'accountTariffHash' => $accountTariff->getHash(),
                                    ],
                                    [
                                        'class' => 'btn btn-danger account-tariff-button-cancel btn-xs',
                                        'title' => 'Отклонить смену тарифа',
                                    ]
                                ) : ''
                            );
                    }
                ],

                [
                    'attribute' => 'actual_from_utc',
                    'format' => 'html',
                    'value' => function (AccountTariffLog $accountTariffLog) {
                        return Yii::$app->formatter->asDate($accountTariffLog->actual_from, 'php:d M Y') .
                            Html::tag('div', $accountTariffLog->actual_from_utc . ' UTC', ['class' => 'small_grey']);
                    }
                ],

                [
                    'attribute' => 'insert_user_id',
                    'format' => 'html',
                    'value' => function (AccountTariffLog $accountTariffLog) {
                        return
                            (
                            $accountTariffLog->insertUser ?
                                $accountTariffLog->insertUser->name :
                                Yii::t('common', '(not set)')
                            ) .
                            (
                            ($accountTariffLog->insert_time && $accountTariffLog->insert_time[0] != '0') ?
                                Html::tag('div', $accountTariffLog->insert_time . ' UTC', ['class' => 'small_grey']) :
                                ''
                            );
                    }
                ],
            ],
        ])
        ?>
    </div>

    <?php if (!$accountTariff->isNewRecord) : ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $formModel->getAccountTariffLogGrid()->query->all(), 'deleteModel' => [new AccountTariffLog(), 'account_tariff_id', $accountTariff->id], 'title' => 'Лог отмен']) ?>
    <?php endif; ?>
</div>
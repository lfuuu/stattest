<?php
/**
 * Создание/редактирование универсальной услуги. Лог смены количество ресурсов
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariff $accountTariff
 * @var \app\modules\uu\models\Resource $resource
 */

// app\classes\grid\GridView добавляет много лишнего
use app\classes\Html;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

?>

<?= $this->render('//layouts/_toggleButton', ['divSelector' => '#account-tariff-resource-log-grid-' . $resource->id, 'title' => 'История изменений']) ?>

<div id="account-tariff-resource-log-grid-<?= $resource->id ?>" class="collapse">
    <?= GridView::widget([
        'dataProvider' => new ActiveDataProvider([
            'query' => $accountTariff->getAccountTariffResourceLogs($resource->id),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]),
        'columns' => [

            [
                'attribute' => 'amount',
                'noWrap' => true,
                'format' => 'html',
                'value' => function (AccountTariffResourceLog $accountTariffResourceLog) {
                    return
                        $accountTariffResourceLog->amount .
                        ' ' .

                        (
                        strtotime($accountTariffResourceLog->actual_from_utc) >= time() ?
                            Html::a(
                                Html::tag('i', '', [
                                    'class' => 'glyphicon glyphicon-erase',
                                    'aria-hidden' => 'true',
                                ]) . ' ' .
                                Yii::t('common', 'Cancel'),
                                [
                                    '/uu/account-tariff/resource-cancel',
                                    'accountTariffId' => $accountTariffResourceLog->account_tariff_id,
                                    'resourceId' => $accountTariffResourceLog->resource_id,
                                ],
                                [
                                    'class' => 'btn btn-danger account-tariff-button-cancel btn-xs',
                                    'title' => 'Отменить смену количества ресурса',
                                ]
                            ) : ''
                        );
                }
            ],

            [
                'attribute' => 'actual_from_utc',
                'format' => 'html',
                'noWrap' => true,
                'value' => function (AccountTariffResourceLog $accountTariffResourceLog) {
                    return Yii::$app->formatter->asDate($accountTariffResourceLog->actual_from, 'php:d M Y') .
                        Html::tag('div', $accountTariffResourceLog->actual_from_utc . ' UTC', ['class' => 'small_grey']);
                }
            ],

            [
                'attribute' => 'insert_user_id',
                'format' => 'html',
                'noWrap' => true,
                'value' => function (AccountTariffResourceLog $accountTariffResourceLog) {
                    return
                        (
                        $accountTariffResourceLog->insertUser ?
                            $accountTariffResourceLog->insertUser->name :
                            Yii::t('common', '(not set)')
                        ) .
                        (
                        ($accountTariffResourceLog->insert_time && $accountTariffResourceLog->insert_time[0] != '0') ?
                            Html::tag('div', $accountTariffResourceLog->insert_time . ' UTC', ['class' => 'small_grey']) :
                            ''
                        );
                }
            ],
        ],
    ])
    ?>
</div>

<?php
/**
 * Создание/редактирование универсальной услуги. Лог тарифов
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use app\classes\DateTimeWithUserTimezone;
use app\classes\uu\model\AccountTariffLog;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<h2><?= Yii::t('tariff', 'Tariff log') ?></h2>

<?php
// добавить тариф (только при редактировании)
if (!$isReadOnly && !$formModel->accountTariff->isNewRecord && !$formModel->accountTariff->isCancelable()) {
    echo $this->render('_editLogForm', ['formModel' => $formModel]);
}
?>

<?= GridView::widget([
    'dataProvider' => $formModel->getAccountTariffLogGrid(),
    'columns' => [

        ['attribute' => 'actual_from'],

        [
            'attribute' => 'tariff_period_id',
            'format' => 'html',
            'value' => function (AccountTariffLog $accountTariffLog) {
                return
                    ($accountTariffLog->tariffPeriod ?
                        Html::a(
                            Html::encode($accountTariffLog->tariffPeriod->getName()),
                            $accountTariffLog->tariffPeriod->getUrl()
                        ) :
                        Yii::t('common', 'Switched off')) .
                    ' ' .

                    (
                    strtotime($accountTariffLog->actual_from) >= time() ?
                        Html::a(
                            'Отменить',
                            Url::toRoute(['/uu/account-tariff/cancel', 'id' => $accountTariffLog->account_tariff_id, 'tariffPeriodId' => $accountTariffLog->tariff_period_id]),
                            [
                                'class' => 'btn btn-danger glyphicon glyphicon-erase account-tariff-button-cancel btn-xs',
                                'title' => 'Отменить смену тарифа',
                            ]
                        ) : ''
                    );
            }
        ],

        [
            'attribute' => 'insert_user_id',
            'value' => function (AccountTariffLog $accountTariffLog) {
                return $accountTariffLog->insertUser ?
                    $accountTariffLog->insertUser->name :
                    Yii::t('common', '(not set)');
            }
        ],

        [
            'attribute' => 'insert_time',
            'value' => function (AccountTariffLog $accountTariffLog) {
                return ($accountTariffLog->insert_time && $accountTariffLog->insert_time[0] != '0') ?
                    (new DateTimeWithUserTimezone($accountTariffLog->insert_time))->getDateTime() :
                    Yii::t('common', '(not set)');
            }
        ],
    ],
]) ?>


<script type='text/javascript'>
    $(function () {

        $(".account-tariff-button-cancel")
            .on("click", function (e, item) {
                return confirm("Отменить смену тарифа?");
            });

    });
</script>

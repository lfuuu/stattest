<?php
/**
 * Создание/редактирование универсальной услуги. Лог тарифов
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use app\classes\uu\model\AccountTariffLog;
use yii\grid\GridView;
use yii\helpers\Html;

?>

<h2><?= Yii::t('tariff', 'Tariff log') ?></h2>

<?php
// добавить тариф (только при редактировании)
if (!$isReadOnly && !$formModel->accountTariff->isNewRecord) {
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
                return $accountTariffLog->tariffPeriod ?
                    Html::a(
                        Html::encode($accountTariffLog->tariffPeriod->getName()),
                        $accountTariffLog->tariffPeriod->getUrl()
                    ) :
                    Yii::t('common', 'Closed');
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

        ['attribute' => 'insert_time'],
    ],
]) ?>


<?php
/**
 * Список счетов
 *
 * @var \yii\web\View $this
 * @var BillFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\BillFilter;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Bill;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Bills'), 'url' => '/uu/bill']
    ],
]) ?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
        [
            'attribute' => 'id',
            'class' => IntegerColumn::className(),
        ],
        [
            'attribute' => 'date',
            'class' => MonthColumn::className(),
            'value' => function (Bill $bill) {
                return Yii::$app->formatter->asDate($bill->date, 'php:M Y');
            }
        ],
        [
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (Bill $bill) {
                return $bill->clientAccount->getLink();
            }
        ],
        [
            'attribute' => 'price',
            'class' => IntegerRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'label' => 'Проводки, у.е.',
            'format' => 'raw',
            'contentOptions' => [
                'class' => 'text-nowrap',
            ],
            'value' => function (Bill $bill) {

                $accountEntries = $bill->accountEntries;
                array_walk($accountEntries, function (&$accountEntry) {
                    /** @var AccountEntry $accountEntry */
                    $accountEntry = sprintf('%s <a href="%s">%.2f</a>',
                        $accountEntry->getTypeName(),
                        $accountEntry->getUrl(),
                        $accountEntry->price);
                });
                return implode('<br />', $accountEntries);

            }
        ],
    ],
]) ?>

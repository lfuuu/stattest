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
use app\classes\grid\column\universal\YesNoColumn;
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
    'extraButtons' =>
//        $this->render('//uu/bill/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('//uu/invoice/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('//uu/balance/_ico', ['clientAccountId' => $filterModel->client_account_id]),
    'columns' => [
        [
            'attribute' => 'id',
            'class' => IntegerColumn::className(),
        ],
        [
            'attribute' => 'is_default',
            'class' => YesNoColumn::className(),
        ],
        [
            'attribute' => 'is_converted',
            'class' => YesNoColumn::className(),
        ],
        [
            'attribute' => 'date',
            'class' => MonthColumn::className(),
            'value' => function (Bill $bill) {
                $format = $bill->is_default ? 'LLL Y' : 'd LLL Y';
                return datefmt_format_object(new DateTime($bill->date), $format, Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
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
        ],
        [
            'label' => 'Проводки, ¤',
            'format' => 'raw',
            'contentOptions' => [
                'class' => 'text-nowrap',
            ],
            'value' => function (Bill $bill) {

                $accountEntries = $bill->accountEntries;
                array_walk($accountEntries, function (&$accountEntry) {
                    /** @var AccountEntry $accountEntry */
                    $accountEntry = $accountEntry->getTypeName() . ' ' .
                        Html::a(
                            sprintf('%.2f', $accountEntry->price),
                            $accountEntry->getUrl()
                        );
                });
                return implode('<br />', $accountEntries);

            }
        ],
    ],
]) ?>

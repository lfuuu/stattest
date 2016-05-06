<?php
/**
 * Бухгалтерский баланс
 *
 * @var \yii\web\View $this
 * @var int $clientAccountId
 * @var AccountEntry[] $accountEntries
 */

use app\classes\grid\GridView;
use app\classes\uu\model\AccountEntry;
use yii\data\ArrayDataProvider;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Balance'),
    ],
]) ?>

<?php
if (!$clientAccountId) {
    Yii::$app->session->setFlash('error', Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
    return;
}

$dataProvider = new ArrayDataProvider([
    'allModels' => $accountEntries,
]);
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'showPageSummary' => true,
    'columns' => [
        [
            'attribute' => 'date',
            'value' => function (AccountEntry $accountEntry) {
                return Yii::$app->formatter->asDate($accountEntry->date, 'php:M Y');
            },
            'pageSummary' => Yii::t('common', 'Summary'),
        ],
        [
            'attribute' => 'price',
//            'format' => ['decimal', 'decimals' => 2],
            'format' => 'html',
            'value' => function (AccountEntry $accountEntry) {
                return sprintf('<a href="%s">%.2f</a>',
                    $accountEntry->getUrl(),
                    $accountEntry->price);
            },
//            'pageSummary' => true,
//            'pageSummaryFunc' => GridView::F_SUM,
        ],
        [
            'attribute' => 'type_id',
            'value' => function (AccountEntry $accountEntry) {
                return $accountEntry->getTypeName();
            },
        ],
    ],
]) ?>

<?php

use app\classes\Html;
use app\modules\sbisTenzor\classes\EdfOperator;
use app\modules\sbisTenzor\helpers\SBISUtils;
use app\modules\sbisTenzor\models\SBISContractor;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var string $title
 */

$baseView = $this;

$this->title = $title;

echo Html::formLabel($title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => $this->title = $title,],
    ],
]);

?>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'clientAccount.organization.name',
            'label' => 'Организация',
            'value'     => function (SBISContractor $model) {
                return SBISUtils::getShortOrganizationName($model->clientAccount->organization);
            },
        ],
        [
            'attribute' => 'clientAccount.contragent.name_full',
            'label' => 'Контагент',
            'format' => 'html',
            'value'     => function (SBISContractor $model) {
                $client = $model->clientAccount;
                if (!$client) {
                    return '-';
                }

                $text = $client->contragent->name_full;

                if ($branchCode = $client->getBranchCode()) {
                    $branchCode = sprintf(' (Код филиала: "%s")', $branchCode);
                }

                return sprintf('%s, %s%s', $client->contragent->id, Html::a($text, $client->getUrl()), $branchCode);
            },
        ],
        [
            'attribute' => 'full_name',
        ],
        [
            'attribute' => 'tin',
            'label' => 'ИНН',
            'value'     => function (SBISContractor $model) {
                return $model->tin ? : ($model->itn ? : '');
            },
        ],
        [
            'attribute' => 'iec',
        ],
        [
            'attribute' => 'is_private',
            'value'     => function (SBISContractor $model) {
                return $model->is_private ? 'Да' : 'Нет';
            },
        ],
        [
            'attribute' => 'exchange_id',
        ],
        [
            'attribute' => 'is_roaming',
            'label' => 'Роуминг',
            'value'     => function (SBISContractor $model) {
                return $model->is_roaming ? 'Вкл' : 'Нет';
            },
        ],
        [
            'attribute' => 'exchange_id',
            'label' => 'Оператор',
            'format' => 'html',
            'value'     => function (SBISContractor $model) {
                $code = substr($model->getEdfId(), 0, 3);
                $operator = new EdfOperator($code);

                return Html::tag('a',
                    $operator->getName(),
                    [
                        'href' => $operator->getUrl(),
                        'target' => '_blank',
                    ]
                );
            },
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['name' => 'Установить', 'url' => '/sbisTenzor/contractor/add']),
    'isFilterButton' => false,
    'floatHeader' => false,
]);
<?php

use app\classes\Html;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\ContractorInfo;
use app\modules\sbisTenzor\classes\EdfOperator;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use app\modules\sbisTenzor\models\SBISContractor;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var int $state
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
            'attribute' => 'contragent.name_full',
            'label' => 'Контагент',
            'format' => 'html',
            'value'     => function (ClientAccount $model) {
                $text = $model->contragent->name_full;
                return Html::a($text, $model->getUrl());
            },
        ],
        [
            'label' => 'Черновики',
            'format' => 'html',
            'value'     => function (ClientAccount $model) {
                return Html::a('Черновики', '/sbisTenzor/draft/?clientId=' . $model->id);
            },
        ],
        [
            'label' => 'Пакеты',
            'format' => 'html',
            'value'     => function (ClientAccount $model) {
                return Html::a('Пакеты', '/sbisTenzor/document/?clientId=' . $model->id);
            },
        ],
        [
            'attribute' => 'exchange_status',
            'format' => 'html',
            'value'     => function (ClientAccount $model) {
                $html = SBISExchangeStatus::getById($model->exchange_status);

                if (SBISExchangeStatus::isVerifiedById($model->exchange_status)) {
                    $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
                } else if (SBISExchangeStatus::isNotApprovedById($model->exchange_status)) {
                    $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
                } else if ($model->exchange_status == SBISExchangeStatus::UNKNOWN) {
                    $html .= '&nbsp;' . Html::tag('strong', '?', ['class' => 'text-warning']);
                }

                return $html;
            },
        ],
        [
            'attribute' => 'is_roaming',
            'label' => 'Доступен',
            'value'     => function (ClientAccount $model) {
                $contractorInfo = ContractorInfo::get($model);
                if ($error = $contractorInfo->getErrorText()) {
                    return 'Нет';
                }

                return $contractorInfo->isRoamingEnabled() ? 'Да' : 'Нет роуминга';
            },
        ],
        [
            'attribute' => 'exchange_id',
            'label' => 'Оператор',
            'format' => 'html',
            'value'     => function (ClientAccount $model) {
                $contractor = SBISContractor::findOne(['account_id' => $model->id]);
                if (!$contractor) {
                    return '';
                }

                $code = substr($contractor->exchange_id, 0, 3);
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
    'extraButtons' =>
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/contractor/',
                'text' => 'Все',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == -1 ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/contractor/?state=' . SBISExchangeStatus::APPROVED,
                'text' => 'Проверенные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISExchangeStatus::APPROVED ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/contractor/?state=' . SBISExchangeStatus::UNKNOWN,
                'text' => 'Не настроены',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISExchangeStatus::UNKNOWN ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/contractor/?state=' . SBISExchangeStatus::DECLINED,
                'text' => 'Проблемные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISExchangeStatus::DECLINED ? 'primary' : 'default'),
            ]
        ),
    'isFilterButton' => false,
    'floatHeader' => false,
]);
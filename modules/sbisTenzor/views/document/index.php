<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\models\SBISDocument;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\grid\ActionColumn;
use app\classes\grid\GridView;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var int $clientId
 * @var int $isAuto
 * @var int $state
 * @var string $title
 * @var int $sendAutoCount
 * @var string $sendAutoConfirmText
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
    if ($clientId && $isAuto) :
?>
    <div class="text-center text-success">
        Для данного клиента включена автоматическая генерация пакетов документов для отправки в СБИС.<br />
        Выключить данную настройку вы можете в <a href="<?= Url::toRoute(['/account/edit', 'id' => $clientId]) ?>">профиле клиента</a>.
    </div>
<?php
    elseif ($clientId) :
?>
    <div class="text-center text-warning">
        Для данного клиента выключена автоматическая генерация пакетов документов для отправки в СБИС.<br />
        Включить данную настройку вы можете в <a href="<?= Url::toRoute(['/account/edit', 'id' => $clientId]) ?>">профиле клиента</a>.
    </div>
<?php
    endif;
?>


<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'label' => 'ID',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->id, $model->getUrl());
            },
        ],
        [
            'attribute' => 'sbis_organization_id',
            'label' => 'Направление',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return
                    Html::a(
                        sprintf(
                            "%s >>> %s",
                            $model->sbisOrganization->organization->name,
                            $model->clientAccount->contragent->name
                        ),
                        $model->getUrl()
                    );
            },
        ],
        [
            'attribute' => 'number',
            'label' => 'Номер',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->number, $model->getUrl());
            },
        ],
        [
            'attribute' => 'date',
            'label' => 'Дата',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->date, $model->getUrl());
            },
        ],
        [
            'attribute' => 'comment',
            'label' => 'Комментарий',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->comment, $model->getUrl());
            },
        ],
        [
            'attribute' => 'attachments',
            'label' => 'Вложений',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return
                    implode(
                        '',
                        array_map(function($attachment) {
                            return
                                sprintf(
                                    '<a href="%s" title="%s"><span class="file20 file_%s"></span></a>',
                                    Url::toRoute([
                                        '/sbisTenzor/document/download-attachment',
                                        'id' => $attachment->id,
                                    ]),
                                    $attachment->file_name,
                                    $attachment->extension
                                );
                        }, $model->attachments)
                    );
            },
        ],
        [
            'attribute' => 'attachments',
            'label' => 'Подписан',
            'format' => 'html',
            'value'     => function (SBISDocument $model) use ($baseView) {
                return
                    $model->isSigned() ?
                        Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) :
                        Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            },
        ],
        [
            'attribute' => 'state',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                $external = $model->external_state_name ? : '';
                $external = $external ? sprintf(' (%s)', $external) : '';
                return Html::a($model->stateName . $external, $model->getUrl());
            },
        ],
        [
            'attribute' => 'created_at',
            'value'     => function (SBISDocument $model) {
                return DateTimeZoneHelper::getDateTime($model->created_at);
            },
        ],
        [
            'attribute' => 'updated_at',
            'value'     => function (SBISDocument $model) {
                return DateTimeZoneHelper::getDateTime($model->updated_at);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{view} {start}',
            'buttons' => [
                'view' => function ($url, SBISDocument $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionView', [
                        'url' => $model->getUrl(),
                    ]);
                },
                'start' => function ($url, SBISDocument $model) use ($baseView) {
                    if ($model->state == SBISDocumentStatus::CREATED) {
                        return $baseView->render('//layouts/_link', [
                            'url' => '/sbisTenzor/document/start?id=' . $model->id,
                            'glyphicon' => 'glyphicon-send text-success',
                            'params' => [
                                'onClick' => 'return confirm("Отправить данный пакет?")',
                            ],
                        ]);
                    }

                    return false;
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'extraButtons' =>
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/document/' . ($clientId ? '?clientId=' . $clientId : ''),
                'text' => 'Активные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == 0 ? 'primary' : 'default'),
            ]
        ) .
            $this->render('//layouts/_buttonLink', [
                    'url' => '/sbisTenzor/document/?state=' . SBISDocumentStatus::CREATED . ($clientId ? '&clientId=' . $clientId : ''),
                    'text' => 'На отправку',
                    'glyphicon' => 'glyphicon-filter',
                    'class' => 'btn-xs btn-' . ($state == SBISDocumentStatus::CREATED ? 'primary' : 'default'),
                ]
            ) .
            $this->render('//layouts/_buttonLink', [
                    'url' => '/sbisTenzor/document/?state=' . SBISDocumentStatus::CANCELLED . ($clientId ? '&clientId=' . $clientId : ''),
                    'text' => 'Отменённые',
                    'glyphicon' => 'glyphicon-filter',
                    'class' => 'btn-xs btn-' . ($state == SBISDocumentStatus::CANCELLED ? 'primary' : 'default'),
                ]
            ) .
            $this->render('//layouts/_buttonLink', [
                    'url' => '/sbisTenzor/document/?state=' . SBISDocumentStatus::ACCEPTED . ($clientId ? '&clientId=' . $clientId : ''),
                    'text' => 'Принятые',
                    'glyphicon' => 'glyphicon-filter',
                    'class' => 'btn-xs btn-' . ($state == SBISDocumentStatus::ACCEPTED ? 'primary' : 'default'),
                ]
            ) .
            '&nbsp;&nbsp;&nbsp;' .
            $this->render('//layouts/_buttonCreate', ['url' => '/sbisTenzor/document/add' . ($clientId ? '?clientId=' . $clientId : '')]) .
            $this->render(
                '//layouts/_link',
                [
                    'url' => '/sbisTenzor/document/send-auto' . ($clientId ? '?clientId=' . $clientId : ''),
                    'text' => sprintf('Отправить подготовленные пакеты (%s)', $sendAutoCount),
                    'glyphicon' => 'glyphicon-send',
                    'params' => [
                        'onClick' => 'return confirm("' . $sendAutoConfirmText . '")',
                        'class' => 'btn btn-success',
                    ],
                ]
            ),
    'isFilterButton' => false,
    'floatHeader' => false,
]);
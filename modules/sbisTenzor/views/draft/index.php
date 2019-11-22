<?php

use app\classes\Html;
use app\modules\sbisTenzor\classes\SBISGeneratedDraftStatus;
use app\modules\sbisTenzor\helpers\SBISDataProvider;
use app\modules\sbisTenzor\models\SBISGeneratedDraft;
use kartik\grid\ActionColumn;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;
use yii\helpers\Url;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var string $title
 * @var int $clientId
 * @var int $isAuto
 * @var int $state
 * @var int $processCount
 * @var string $processConfirmText
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
            'value'     => function (SBISGeneratedDraft $model) {
                return Html::a($model->id, $model->getUrl());
            },
        ],
        [
            'attribute' => 'invoice.organization.id',
            'label' => 'Организация',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                $organization = $model->invoice->organization;

                return $organization->name;
            },
        ],
        [
            'attribute' => 'invoice.bill.clientAccount.contragent.name_full',
            'label' => 'Контагент',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                $client = $model->invoice->bill->clientAccount;

                $text = $client->contragent->name_full;
                return Html::a($text, $client->getUrl());
            },
        ],
        [
            'attribute' => 'invoice.bill.clientAccount.contragent.name_full',
            'label' => 'Файлы',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                $client = $model->invoice->bill->clientAccount;

                $html = '';
                if ($document = $model->document) {
                    foreach ($document->attachments as $attachment) {
                        $html .=
                            ($html ? '<br />' : '') .
                            Html::tag('a',
                                '<span class="file20 file_' . $attachment->extension . '"></span> Вложение #' . $attachment->number,
                                ['href' => \yii\helpers\Url::toRoute([
                                    '/sbisTenzor/document/download-attachment',
                                    'id' => $attachment->id,
                                ])]
                            );
                    }
                } elseif ($client->exchangeGroup) {
                    $i = 0;
                    foreach ($client->exchangeGroup->getExchangeFiles() as $exchangeFile) {
                        $html .=
                            ($html ? '<br />' : '') .
                            Html::tag('a',
                                '<span class="file20 file_' . $exchangeFile->extension . '"></span> ' . $exchangeFile->form->name,
                                ['href' => \yii\helpers\Url::toRoute([
                                    '/sbisTenzor/draft/download-attachment',
                                    'id' => $model->id,
                                    'number' => $i++,
                                ])]
                            );
                    }
                }

                return Html::tag('span', $html ? : '???', ['class' => 'text-nowrap']);
            },
        ],
        [
            'attribute' => 'invoice_id',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                $invoice = $model->invoice;
                $text = sprintf('#%s: №%s от %s на сумму %s %s<br/ >Счёт №%s от %s', $invoice->id, $invoice->number, $invoice->date, number_format($invoice->sum, 2, '.', ''), $invoice->bill->currency, $invoice->bill->bill_no, $invoice->bill->bill_date);
                return Html::a($text, $invoice->bill->getUrl());
            },
        ],
        [
            'attribute' => 'state',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                return Html::tag(
                    'i',
                    '&nbsp;' . SBISGeneratedDraftStatus::getById($model->state),
                    [
                        'class' => sprintf(
                            'glyphicon %s %s text-nowrap',
                                SBISGeneratedDraftStatus::getIconById($model->state),
                                SBISGeneratedDraftStatus::getTextClassById($model->state)
                        ),
                    ]
                );
            },
        ],
        [
            'attribute' => 'sbis_document_id',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                $html = 'Не создан';

                if ($document = $model->document) {
                    $html = sprintf('%s от %s', $document->comment, $document->date);
                    $html = Html::a($html, $document->getUrl());;
                }

                return $html;
            },
        ],
        [
            'attribute' => 'created_at',
        ],
        [
            'attribute' => 'updated_at',
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{cancel} {save}',
            'buttons' => [
                'cancel' => function ($url, SBISGeneratedDraft $model) use ($baseView) {
                    if ($model->state == SBISGeneratedDraftStatus::DRAFT) {
                        return $baseView->render('//layouts/_actionDisable', [
                            'url' => '/sbisTenzor/draft/cancel?id=' . $model->id,
                        ]);
                    }
                    if ($model->state == SBISGeneratedDraftStatus::CANCELLED) {
                        return $baseView->render('//layouts/_actionEnable', [
                            'url' => '/sbisTenzor/draft/restore?id=' . $model->id,
                        ]);
                    }

                    return '';
                },
                'save' => function ($url, SBISGeneratedDraft $model) use ($baseView) {
                    if ($model->state == SBISGeneratedDraftStatus::DRAFT) {
                        return $baseView->render('//layouts/_link', [
                            'url' => '/sbisTenzor/draft/save?id=' . $model->id,
                            'glyphicon' => 'glyphicon-play text-success',
                            'params' => [
                                'onClick' => 'return confirm("Создать пакет по данному черновику?")',
                                'title' => 'Создать пакет документов'
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
                'url' => '/sbisTenzor/draft/' . ($clientId ? '?clientId=' . $clientId : ''),
                'text' => 'На создание',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == 0 ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::CANCELLED . ($clientId ? '&clientId=' . $clientId : ''),
                'text' => 'Отменённые',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::CANCELLED ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::DONE . ($clientId ? '&clientId=' . $clientId : ''),
                'text' => 'Обработанные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::DONE ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::ERROR . ($clientId ? '&clientId=' . $clientId : ''),
                'text' => 'Ошибка',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::ERROR ? 'primary' : 'default'),
            ]
        ) .
        '&nbsp;&nbsp;&nbsp;' .
        $this->render(
            '//layouts/_link',
            [
                'url' => '/sbisTenzor/draft/process' . ($clientId ? '?clientId=' . $clientId : ''),
                'text' => sprintf('Создать пакеты на основе черновиков (%s)', $processCount),
                'glyphicon' => 'glyphicon-send',
                'params' => [
                    'onClick' => 'return confirm("' . $processConfirmText . '")',
                    'class' => 'btn btn-success',
                ],
            ]
        ),
    'isFilterButton' => false,
    'floatHeader' => false,
]);
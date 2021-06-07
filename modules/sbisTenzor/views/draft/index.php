<?php

use app\classes\Html;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use app\modules\sbisTenzor\classes\SBISGeneratedDraftStatus;
use app\modules\sbisTenzor\classes\XmlGenerator;
use app\modules\sbisTenzor\helpers\SBISUtils;
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
 * @var int $isVerified
 * @var int $state
 * @var int $processCount
 * @var bool $showAll
 * @var int $processCountAll
 * @var string $processConfirmText
 * @var string $processAllConfirmText
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
    <div class="text-right">
        <?= $this->render('//layouts/_buttonLink', [
            'url' => \Yii::$app->getRequest()->getUrl(),
            'text' => 'Обновить',
            'glyphicon' => 'glyphicon-refresh',
        ])?>
    </div>

<?php
if ($clientId && $isAuto) :
    ?>
    <div class="text-center text-success">
        Для данного клиента включена автоматическая генерация пакетов документов для отправки в СБИС.<br />
        Выключить данную настройку вы можете в <a href="<?= Url::toRoute(['/account/edit', 'id' => $clientId]) ?>">профиле клиента</a>.
        <?=$isVerified ? '<br />' . Html::tag('strong', 'Клиент в списке проверенных', ['class' => 'text-success']) : ''?>
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

                return $html = Html::tag(
                    'span',
                    SBISUtils::getShortOrganizationName($organization),
                    ['class' => 'text-nowrap']
                );
            },
        ],
        [
            'attribute' => 'invoice.bill.clientAccount.contragent.name_full',
            'label' => 'Контагент',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                if (!$model->invoice->bill) {
                    return 'Не найден счёт.';
                }
                if (!$model->invoice->bill->clientAccount) {
                    return 'Не найден клиент для данного счёта.';
                }

                $client = $model->invoice->bill->clientAccount;

                $html = $client->contragent->name_full;
                if (SBISExchangeStatus::isVerifiedById($client->exchange_status)) {
                    $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
                } else if (SBISExchangeStatus::isNotApprovedById($client->exchange_status)) {
                    $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
                } else if ($client->exchange_status == SBISExchangeStatus::UNKNOWN) {
                    $html .= '&nbsp;' . Html::tag('strong', '?', ['class' => 'text-warning']);
                }

                return Html::a($html, $client->getUrl());
            },
        ],
        [
            'label' => 'Файлы',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                if (!$model->invoice->bill) {
                    return 'Не найден счёт.';
                }
                if (!$model->invoice->bill->clientAccount) {
                    return 'Не найден клиент для данного счёта.';
                }

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

                        if ($exchangeFile->isXML()) {
                            try {
                                $xmlFile = XmlGenerator::createXmlGenerator($exchangeFile->form, $model->invoice);
                                $errorText = $xmlFile->getErrorText();
                            } catch (\Exception $e) {
                                $errorText = $e->getMessage();
                            }

                            if ($errorText) {
                                $html .= '<span class="text-danger" title="' . htmlspecialchars($errorText) . '"><i class="glyphicon glyphicon-remove"></i>&nbsp;Ошибки</span>';
                            } else {
                                $html .= ' <span class="text-success" title="Проверен"><i class="glyphicon glyphicon-ok-circle"></i></span>';
                            }
                        }
                    }
                }

                return Html::tag('span', $html ? : '???', ['class' => 'text-nowrap']);
            },
        ],
        [
            'attribute' => 'invoice_id',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                if (!$model->invoice->bill) {
                    return 'Не найден счёт.';
                }
                $invoice = $model->invoice;

                $html = Html::tag(
                    'span',
                    sprintf('#%s: №%s от %s', $invoice->id, $invoice->number, $invoice->date),
                    ['class' => 'text-nowrap']
                );
                $html = sprintf('%s<br />Сумма: %s %s', $html, number_format($invoice->sum, 2, '.', ''), $invoice->bill->currency);
                $html = sprintf('%s<br/ ><small>Счёт №%s от %s</small>', Html::a($html, $invoice->bill->getUrl()), $invoice->bill->bill_no, $invoice->bill->bill_date);

                return $html;
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
                        'title' => $model->errors
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
                    $html = Html::a($html, $document->getUrl());
                }

                return $html;
            },
        ],
        [
            'attribute' => 'warnings',
            'label' => 'Информация',
            'format' => 'html',
            'value'     => function (SBISGeneratedDraft $model) {
                if (!$model->invoice->bill) {
                    return 'Не найден счёт.';
                }
                if (!$model->invoice->bill->clientAccount) {
                    return 'Не найден клиент для данного счёта.';
                }

                $html = '';

                $model->checkForWarnings(true);
                if ($warning = $model->warnings) {
                    $html .=
                        '<span class="text-danger" title="Исправьте ошибку, иначе пакет документов не будет создан!">' .
                            '<i class="glyphicon glyphicon-exclamation-sign"></i>&nbsp;' .
                            htmlspecialchars($warning) .
                        '</span>';
                }

                return $html;
            },
        ],
        [
            'attribute' => 'created_at',
        ],
        [
            'attribute' => 'updated_at',
            'value'     => function (SBISGeneratedDraft $model) {
                $html = $model->updated_at;

                return $html ? : '';
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{cancel} {save}',
            'buttons' => [
                'cancel' => function ($url, SBISGeneratedDraft $model) use ($baseView) {
                    if (!$model->invoice->bill) {
                        return '';
                    }
                    if (!$model->invoice->bill->clientAccount) {
                        return '';
                    }

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
                        if (!$model->invoice->bill) {
                            return '';
                        }
                        if (!$model->invoice->bill->clientAccount) {
                            return '';
                        }

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
                'url' => '/sbisTenzor/draft/',
                'text' => 'На создание',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == 0 ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::CANCELLED,
                'text' => 'Отменённые',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::CANCELLED ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::DONE,
                'text' => 'Обработанные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::DONE ? 'primary' : 'default'),
            ]
        ) .
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/draft/?state=' . SBISGeneratedDraftStatus::ERROR,
                'text' => 'Ошибка',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == SBISGeneratedDraftStatus::ERROR ? 'primary' : 'default'),
            ]
        ) .
        '&nbsp;&nbsp;&nbsp;' .
        (
            $processCount ?
                $this->render(
                    '//layouts/_link',
                    [
                        'url' => '/sbisTenzor/draft/process',
                        'text' => sprintf('Создать пакеты по клиенту (%s)', $processCount),
                        'glyphicon' => 'glyphicon-arrow-down',
                        'params' => [
                            'onClick' => 'return confirm("' . $processConfirmText . '")',
                            'class' => 'btn btn-success',
                        ],
                    ]
                ) .
                '&nbsp;' :
                ''
        ) .
        (
            $showAll ?
                $this->render(
                    '//layouts/_link',
                    [
                        'url' => '/sbisTenzor/draft/process-all',
                        'text' => sprintf('Создать пакеты для всех подтвержденных (%s)', $processCountAll),
                        'glyphicon' => 'glyphicon-sort-by-attributes',
                        'params' => [
                            'onClick' => 'return confirm("' . $processAllConfirmText . '")',
                            'class' => 'btn btn-success',
                        ],
                    ]
                ) :
                ''
        ),
    'isFilterButton' => false,
    'floatHeader' => false,
]);
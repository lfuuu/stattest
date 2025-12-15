<?php
/**
 * Предпросмотр файла (шаг 3/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFile $countryFile
 * @var bool $clear
 * @var bool $checkFull
 * @var bool $runCheck
 * @var bool $isSmall
 * @var int $offset
 * @var int $limit
 * @var Form $formModel
 * @var app\models\EventQueue|null $previewEvent
 */

use app\models\EventQueue;
use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp2\forms\import\Form;
use app\modules\nnp\classes\helpers\ImportPreviewHelper;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$country = $countryFile->country;

$form = ActiveForm::begin();
$baseStep3Params = [
    'countryCode' => $country->code,
    'fileId' => $countryFile->id,
    'runCheck' => 1,
];
$startQueueParams = [
    'countryCode' => $country->code,
    'fileId' => $countryFile->id,
    'startQueue' => 1,
];
$progressData = $previewEvent ? ImportPreviewHelper::getProgressData($previewEvent) : [0, 0, 'info', 0, '', ''];
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Импорт', 'url' => '/nnp/import/'],
        ['label' => $country->name_rus, 'url' => Url::to(['/nnp/import/step2/', 'countryCode' => $country->code])],
        ['label' => $this->title = 'Предпросмотр файла (шаг 3/3)'],
    ],
]) ?>

<?= $this->render('//layouts/_buttonLink', [
    'url' => Url::to(['/nnp/import/step2', 'countryCode' => $country->code]),
    'text' => 'Другой файл',
    'glyphicon' => 'glyphicon-step-backward',
    'class' => 'btn-default',
]) ?>

<?php if ($previewEvent && $previewEvent->status !== EventQueue::STATUS_OK): ?>
    <div class="row">
        <div class="col-sm-12">
            <?php if (!$progressData[4] && $progressData[2] !== 'success'): ?>
                <script>
                    setTimeout(function () {
                        window.location.reload(false);
                    }, 4000);
                </script>
            <?php endif; ?>

            <b><?= $progressData[3] ?>%</b> (<?= $progressData[1] ?> из <?= $progressData[0] ?>)<br>
            <?php if ($progressData[5]): ?>
                <small class="text-muted"><?= Html::encode($progressData[5]) ?></small><br>
            <?php endif; ?>

            <?php if ($progressData[4]): ?>
                <div class="alert alert-danger"><?= Html::encode($progressData[4]) ?></div>
                <?= $this->render('//layouts/_link', [
                    'url' => Url::to(array_merge(['/nnp/import/step3'], $startQueueParams)),
                    'text' => 'Запустить проверку повторно',
                    'glyphicon' => 'glyphicon-refresh',
                    'params' => ['class' => 'btn btn-warning'],
                ]) ?>
            <?php else: ?>
                <div class="progress">
                    <div class="progress-bar progress-bar-<?= $progressData[2] ?> progress-bar-striped" role="progressbar" aria-valuenow="<?= $progressData[3] ?>"
                        aria-valuemin="0" aria-valuemax="100" style="width:<?= $progressData[3] ?>%">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
    ActiveForm::end();
    return;
endif; ?>

<?php if (!$runCheck): ?>
    <div class="row">
        <div class="col-sm-12">
            <?= Html::tag('div', 'Проверка файла готовится. Дождитесь завершения очереди или запустите повторно.', ['class' => 'alert alert-info']) ?>
            <?= $this->render('//layouts/_link', [
                'url' => Url::to(array_merge(['/nnp/import/step3'], $startQueueParams)),
                'text' => 'Запустить проверку файла',
                'glyphicon' => 'glyphicon-play',
                'params' => ['class' => 'btn btn-primary'],
            ]) ?>
        </div>
    </div>
<?php
    ActiveForm::end();
    return;
endif; ?>

<div class="row">
    <div class="col-sm-12">
        <?php
        $checkMessage = $previewEvent && $previewEvent->status === EventQueue::STATUS_OK
            ? 'Проверка файла завершена очередью.'
            : 'Проверка файла запущена' . ($checkFull ? ' полностью.' : sprintf(' для строк %d–%d.', $offset + 1, $offset + $limit));
        ?>
        <?= Html::tag('div', $checkMessage, ['class' => 'alert alert-success']) ?>
    </div>
</div>

<?php
$filePath = $country->getMediaManager()->getUnzippedFilePath($countryFile);
$handle = fopen($filePath, 'r');
if (!$handle) {
    echo 'Ошибка чтения файла ' . $filePath . ': ' . error_get_last();
    return;
}

$delimiter = ImportPreviewHelper::detectDelimiter($handle);

$expectedHeader = ImportPreviewHelper::getExpectedHeader();

$rowNumber = 0;
$isFileOK = true;
$errorLines = [];

$warningLines = [];
$alreadyRead = [];
$rangesByPrefix = [];
$segmentsMeta = [];

$importServiceUploaded = new ImportServiceUploaded(['countryCode' => $country->code]);
$isButtonShown = false;

$useCache = !$clear;
$cachedRecords = [];
$recordsCoverage = 'full';
$hasCachedRecords = false;

if ($useCache) {
    if ($cachedData = $countryFile->getCachedPreviewData()) {
        $cachedData = json_decode($cachedData, true);
        $checkFull = $checkFull && empty($cachedData['checked']);
        if (!$checkFull) {
            $isFileOK = $cachedData['isFileOK'];
            $errorLines = $cachedData['errorLines'];
            $warningLines = $cachedData['warningLines'];

            $cachedRecords = $cachedData['records'];
            $recordsCoverage = $cachedData['recordsCoverage'] ?? 'full';
            $hasCachedRecords = !empty($cachedRecords);
        }
    }
} else {
    $countryFile->removeCachedPreviewData();
}

// Если очередь завершилась успешно и в кэше нет строк (сохранялись только проблемные записи),
// нет смысла прогружать весь файл ради предпросмотра — сразу показываем успешный результат.
if (
    $previewEvent &&
    $previewEvent->status === EventQueue::STATUS_OK &&
    $useCache &&
    !$hasCachedRecords &&
    $recordsCoverage !== 'full'
) {
    echo Html::tag('div', 'Проверка файла завершена. Ошибок и предупреждений нет.', ['class' => 'alert alert-success']);
    ActiveForm::end();
    return;
}
?>

<?php if ($cachedRecords): ?>
<div class="row">
    <div class="col-sm-6">
<?php
    echo 'Превью файла прочитано из кэша. ' .
        Html::a(
            'сбросить кэш',
            Url::to(array_merge(['/nnp/import/step3'], $baseStep3Params, ['clear' => 1]))
        );
?>
    </div>
</div>
<?php endif ?>

<?php if (!$checkFull): ?>
<div class="row">
    <div class="col-sm-6">
<?php
    echo 'Проверка файла произведена лишь частично. ' .
        Html::a(
            'проверить полностью',
            Url::to(array_merge(['/nnp/import/step3'], $baseStep3Params, ['check' => 1]))
        );
?>
    </div>
</div>
<?php endif ?>

<?php ob_start() ?>

    <table class="table">

        <?= $this->render('_step3_th') ?>

        <?php
        // ############################################
        // ### Start reading
        // ############################################
        $records = [];
        $headerChecked = false;
        $useCachedRecords = $recordsCoverage !== 'full' || $hasCachedRecords;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) : ?>

            <?php
            $rowNumber++;

            if ($rowNumber == 1 && !is_numeric($row[0])) {
                if (!$headerChecked) {
                    $headerChecked = true;
                    $headerResult  = ImportPreviewHelper::validateHeaderRow($row);

                    if (!empty($headerResult['errors'])) {
                        $isFileOK               = false;
                        $errorLines[$rowNumber] = implode(PHP_EOL, $headerResult['errors']);
                    }
                    if (!empty($headerResult['warnings'])) {
                        $warningLines[$rowNumber] =
                            (isset($warningLines[$rowNumber]) && $warningLines[$rowNumber] !== '' ? $warningLines[$rowNumber] . PHP_EOL : '') .
                            implode(PHP_EOL, $headerResult['warnings']);
                    }
                }
                // шапку в таблицу не выводим
                continue;
            }

            if ($useCachedRecords) {
                $rowStatus = $cachedRecords[$rowNumber][0] ?? array_fill(0, count($expectedHeader), ImportPreviewHelper::STATUS_OK);
                $oldLine = $cachedRecords[$rowNumber][1] ?? null;
            } else {
                if (
                    !$checkFull &&
                    (
                        ($rowNumber < $offset) ||
                        ($rowNumber > $offset + $limit)
                    )
                ) {
                    continue;
                }

                [$rowStatus, $oldLine] = ImportPreviewHelper::checkRow(
                    $rowNumber,
                    $row,
                    $importServiceUploaded,
                    $errorLines,
                    $warningLines,
                    $alreadyRead,
                    $rangesByPrefix,
                    $segmentsMeta,
                    $country->code,
                    $countryFile->id,
                    $isFileOK
                );
                $records[$rowNumber] = [
                    $rowStatus,
                    $oldLine,
                ];
            }

            if ($rowNumber < $offset) :
                // пропустить эти строки
                if ($rowNumber == 1) :
                    // только 1 раз вывести ссылку "назад"
                    $newOffset = max(0, $offset - $limit);
                    ?>
                    <tr>
                        <td colspan="12">
                            <?= $this->render('//layouts/_buttonLink', [
                                'url' => Url::to(array_merge(['/nnp/import/step3'], $baseStep3Params, ['offset' => $newOffset, 'limit' => $limit])),
                                'text' => sprintf('Проверить предыдущие строки (%d - %d)', $newOffset + 1, $newOffset + $limit),
                                'glyphicon' => 'glyphicon-menu-up',
                                'class' => 'btn-default btn-xs',
                            ]) ?>
                        </td>
                    </tr>
                    <?php
                endif;
                continue;
            elseif ($rowNumber > $offset + $limit) :
                // уже проверили достаточно
                // вывести ссылку "вперед"
                $newOffset = $offset + $limit;
                if (!$isButtonShown) {
                    ?>
                    <tr>
                        <td colspan="12">
                            <?= $this->render('//layouts/_buttonLink', [
                                'url' => Url::to(array_merge(['/nnp/import/step3'], $baseStep3Params, ['offset' => $newOffset, 'limit' => $limit])),
                                'text' => sprintf('Проверить следующие строки (%d - %d)', $newOffset + 1, $newOffset + $limit),
                                'glyphicon' => 'glyphicon-menu-down',
                                'class' => 'btn-default btn-xs',
                            ]) ?>
                        </td>
                    </tr>
                    <?php
                    $isButtonShown = true;
                }
                if (!$checkFull) {
                    break;
                }
                continue;
            endif;
            ?>
            <tr>
                <?php
                echo Html::tag(
                    'td',
                    $rowNumber .
                        '<a name="line' . $rowNumber . '" style="position: relative;top: -85px;"></a>',
                    [
                        'class' => isset($warningLines[$rowNumber]) ? 'warning' : ''
                    ]
                );
                foreach ($row as $columnIndex => $cellValue) {
                    $status = $rowStatus[$columnIndex] ?? ImportPreviewHelper::STATUS_OK;

                    switch ($status) {
                        case ImportPreviewHelper::STATUS_ERROR:
                            $class = 'danger';
                            $isFileOK = false;
                            break;
                        case ImportPreviewHelper::STATUS_WARNING:
                            $class = 'warning';
                            break;
                        default:
                            $class = 'success';
                    }

                    echo Html::tag('td', $cellValue, ['class' => $class]);
                }
                ?>
            </tr>
        <?php endwhile ?>

        <?php
            if ($useCache && !$cachedRecords && $records) {
                $data = [
                    'isFileOK' => $isFileOK,
                    'records' => $records,
                    'errorLines' => $errorLines,
                    'warningLines' => $warningLines,
                    'checked' => $checkFull,
                    'recordsCoverage' => $checkFull ? 'full' : 'partial',
                ];
                $countryFile->setCachedPreviewData(json_encode($data));
            }
        ?>

    </table>

<?php
fclose($handle);
$content = ob_get_clean();

if ($isFileOK) {
    if ($warningLines) {
        echo Html::tag('div', 'Предупреждения (' . count($warningLines) . '):', ['class' => 'alert alert-warning']);
        echo "<ul>";
        foreach ($warningLines as $line => $text) {
            echo Html::tag(
                'li',
                //'Строка ' . $line,
                    Html::a(
                        'Строка ' . $line,
                        Url::to(array_merge([
                            '/nnp/import/step3',
                        ], $baseStep3Params, [
                            'offset' => $line,
                            'limit' => $limit,
                        ]))
                    ) . ': ' . $text,
                ['class' => 'alert alert-warning']
            );
        }
        echo "</ul>";
    }

//    if (!NumberRange::isTriggerEnabled()) {
        $params = [
            'class' => 'btn btn-success',
            'id' => 'btnSubmit',
        ];

        if ($warningLines) {
            $params['onClick'] = 'return confirm("Вы уверены?")';
        }

?>
<div class="row">
    <div class="col-sm-2">
<?php
    echo $form->field($formModel, 'version')->widget(Select2::class, [
        'data' => $formModel->getVersions(),
        'pluginEvents' => [
            'change' => 'function() { 
                $("#btnSubmit").attr(
                    "href",
                    "' . Url::to(['/nnp/import/step4', 'countryCode' => $country->code, 'fileId' => $countryFile->id]) . '&version=" + $(this).val()
                );
            }',
        ],
    ])->label('Версия импорта: ');
?>
    </div>
</div>
<?php

    $buttonText = 'Импортировать файл';
    if (!$checkFull) {
        $buttonText = 'Импортировать непроверенный файл';
        $params['class'] = 'btn btn-warning';
        $params['onClick'] = 'return confirm("Файл не проверен полностью.\nВозможны ошибки при импорте!\nПродолжить?")';
    }
    echo $this->render('//layouts/_link', [
        'url' => Url::to(['/nnp/import/step4', 'countryCode' => $country->code, 'fileId' => $countryFile->id, 'version' => $formModel->version]),
        'text' => $buttonText,
        'glyphicon' => 'glyphicon-fast-forward',
        'params' => $params,
    ]);

    if ($isSmall) {
        echo '&nbsp;';
        echo $this->render('//layouts/_link', [
            'url' => Url::to([
                '/nnp/import/step4',
                'countryCode' => $country->code,
                'fileId' => $countryFile->id,
                'version' => $formModel->version,
                'queue' => 1,
            ]),
            'text' => 'Поставить импорт в очередь',
            'glyphicon' => 'glyphicon-time',
            'params' => [
                'class' => 'btn btn-info',
                'id' => 'btnQueueImport',
            ],
        ]);
    }
//    }
} else {
    echo Html::tag('div', 'Импорт невозможен, потому что файл содержит ошибки (' . count($errorLines) . '). Исправьте ошибки в файле и загрузите его заново.', ['class' => 'alert alert-danger']);
    if ($errorLines) {
        echo "<ul>";
        foreach ($errorLines as $line => $text) {
            echo Html::tag(
                'li',
                Html::a(
                    'Строка ' . $line,
                    Url::to(array_merge([
                        '/nnp/import/step3',
                    ], $baseStep3Params, [
                        'offset' => $line,
                        'limit' => $limit,
                    ])) . '#line' . $oldLine
                ) . ': ' . nl2br($text),
                ['class' => 'alert alert-danger']
            );
        }
        echo "</ul>";
    }
}

ActiveForm::end();

echo $content;
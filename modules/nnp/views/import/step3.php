<?php
/**
 * Предпросмотр файла (шаг 3/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFile $countryFile
 * @var bool $clear
 * @var bool $checkFull
 * @var int $offset
 * @var int $limit
 * @var Form $formModel
 */

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

$importServiceUploaded = new ImportServiceUploaded(['countryCode' => $country->code]);
$isButtonShown = false;

$useCache = !$clear;
$cachedRecords = [];
if ($useCache) {
    if ($cachedData = $countryFile->getCachedPreviewData()) {
        $cachedData = json_decode($cachedData, true);
        $checkFull = $checkFull && empty($cachedRecords['checked']);
        if (!$checkFull) {
            $isFileOK = $cachedData['isFileOK'];
            $errorLines = $cachedData['errorLines'];
            $warningLines = $cachedData['warningLines'];

            $cachedRecords = $cachedData['records'];
        }
    }
} else {
    $countryFile->removeCachedPreviewData();
}
?>

<?php if ($cachedRecords): ?>
<div class="row">
    <div class="col-sm-6">
<?php
    echo 'Превью файла прочитано из кэша. ' .
        Html::a(
            'сбросить кэш',
            Url::to([
                '/nnp/import/step3',
                'countryCode' => $country->code,
                'fileId' => $countryFile->id,
                'clear' => 1,
            ])
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
            Url::to([
                '/nnp/import/step3',
                'countryCode' => $country->code,
                'fileId' => $countryFile->id,
                'check' => 1,
            ])
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

        while (($row = fgetcsv($handle, $rowLength = 4096, $delimiter)) !== false) : ?>

            <?php
            $rowNumber++;

            if ($cachedRecords) {
                $rowStatus = $cachedRecords[$rowNumber][0];
                $oldLine = $cachedRecords[$rowNumber][1];
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

                list($rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix) = ImportPreviewHelper::checkRow($rowNumber, $row, $importServiceUploaded, $errorLines, $warningLines, $alreadyRead, $rangesByPrefix, $country->code, $countryFile->id, $isFileOK);
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
                                'url' => Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id, 'offset' => $newOffset, 'limit' => $limit]),
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
                                'url' => Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id, 'offset' => $newOffset, 'limit' => $limit]),
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
                    Url::to([
                        '/nnp/import/step3',
                        'countryCode' => $country->code,
                        'fileId' => $countryFile->id,
                        'offset' => $line,
                        'limit' => $limit,
                    ])
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
                    Url::to([
                        '/nnp/import/step3',
                        'countryCode' => $country->code,
                        'fileId' => $countryFile->id,
                        'offset' => $line,
                        'limit' => $limit,
                    ]) . '#line' . $oldLine
                ) . ': ' . nl2br($text),
                ['class' => 'alert alert-danger']
            );
        }
        echo "</ul>";
    }
}

ActiveForm::end();

echo $content;
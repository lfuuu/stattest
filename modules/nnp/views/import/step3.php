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

$rowNumber = 0;
$isFileOK = true;
$errorLines = [];

$warningLines = [];
$alreadyRead = [];

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

        <?php /**
         * @param int $lineNumber
         * @param array|null $row
         * @param ImportServiceUploaded $importServiceUploaded
         * @param array $errorLines
         * @param array $warningLines
         * @param array $alreadyRead
         * @param int $countryCode
         * @param int $countryFileId
         * @return array
         */
        function checkIfValid(int $lineNumber, array $row, ImportServiceUploaded $importServiceUploaded, array $errorLines, array $warningLines, array $alreadyRead, $countryCode, $countryFileId, $isFileOK): array
        {
            $oldLine = null;
            if (!($lineNumber == 1 && !is_numeric($row[0]))) {
                $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);
                $rowStatus = $importServiceUploaded->getRowHasError($numberRangeImport);

                $key = sprintf("(%s) %s %s - %s %s", $numberRangeImport->country_prefix, $numberRangeImport->ndc_str, $numberRangeImport->number_from, $numberRangeImport->ndc_str, $numberRangeImport->number_to);
                if ($errors = $numberRangeImport->getErrors()) {
                    $isFileOK = false;

                    $text = '';
                    foreach ($errors as $key => $errorList) {
                        foreach ($errorList as $value) {
                            $text .= sprintf("%s: %s", $key, $value) . PHP_EOL;
                        }
                    }
                    $errorLines[$lineNumber] = $text;
                } elseif (empty($numberRangeImport->ndc_str) && $numberRangeImport->ndc_str != '0') {
                    $warningLines[$lineNumber] = 'Пустой NDC - диапазон не будет загружен';
                } elseif ($numberRangeImport->ndc_type_id == 6) {
                    $warningLines[$lineNumber] = 'Короткий номер - диапазон не будет загружен';
                } elseif (isset($alreadyRead[$key])) {
                    $oldLine = $alreadyRead[$key];

                    $warningLines[$lineNumber] = "Диапазон $key уже добавлен в " . Html::a(
                            'строке ' . $oldLine,
                            Url::to([
                                '/nnp/import/step3',
                                'countryCode' => $countryCode,
                                'fileId' => $countryFileId,
                                'offset' => $oldLine,
                                'limit' => min($lineNumber - $oldLine, 100)
                            ]) . '#line' . $oldLine
                        );
                }

                $alreadyRead[$key] = $lineNumber;
            }

            return array($rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead);
        }

        // ############################################
        // ### Start reading
        // ############################################
        $records = [];
        while (($row = fgetcsv($handle, $rowLength = 4096, $delimiter = ';')) !== false) : ?>

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

                list($rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead) = checkIfValid($rowNumber, $row, $importServiceUploaded, $errorLines, $warningLines, $alreadyRead, $country->code, $countryFile->id, $isFileOK);
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

            if ($rowNumber == 1 && !is_numeric($row[0])) {
                // Шапка (первая строчка с названиями полей) - пропустить
                continue;
            }
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
                    echo Html::tag('td', $cellValue, ['class' => $rowStatus[$columnIndex] ? 'danger' : 'success']);
                    if ($rowStatus[$columnIndex]) {
                        $isFileOK = false;
                    }
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
<?php
/**
 * Предпросмотр файла (шаг 3/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFile $countryFile
 * @var int $offset
 * @var int $limit
 */

use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp\models\NumberRange;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$country = $countryFile->country;
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
?>

<?php ob_start() ?>

    <table class="table">

        <?= $this->render('_step3_th') ?>

        <?php while (($row = fgetcsv($handle, $rowLength = 4096, $delimiter = ';')) !== false) : ?>

            <?php
            $rowNumber++;

            if (!($rowNumber == 1 && !is_numeric($row[0]))) {
                $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);
                $rowStatus = $importServiceUploaded->getRowHasError($numberRangeImport);

                $key = $numberRangeImport->number_from . '-' . $numberRangeImport->ndc . $numberRangeImport->number_to;
                $key = sprintf("%s %s - %s %s", $numberRangeImport->ndc, $numberRangeImport->number_from, $numberRangeImport->ndc, $numberRangeImport->number_to);
                if ($numberRangeImport->getErrors()) {
                    $isFileOK = false;
                    $errorLines[] = $rowNumber;
                } elseif (isset($alreadyRead[$key])) {
                    $oldLine = $alreadyRead[$key];

                    $warningLines[$rowNumber] = "Диапазон $key уже добавлен в " . Html::a(
                            'строке ' . $oldLine,
                            Url::to([
                                '/nnp/import/step3',
                                'countryCode' => $country->code,
                                'fileId' => $countryFile->id,
                                'offset' => $oldLine,
                                'limit' => $limit,
                            ])
                        );
                }

                $alreadyRead[$key] = $rowNumber;
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
                //break;
                continue;
            endif;

            if ($rowNumber == 1 && !is_numeric($row[0])) {
                // Шапка (первая строчка с названиями полей) - пропустить
                continue;
            }
            ?>
            <tr>
                <?php
//                $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);
//                $rowStatus = $importServiceUploaded->getRowHasError($numberRangeImport);

                echo Html::tag('td', $rowNumber, []);
                foreach ($row as $columnIndex => $cellValue) {
                    echo Html::tag('td', $cellValue, ['class' => $rowStatus[$columnIndex] ? 'danger' : 'success']);
                    if ($rowStatus[$columnIndex]) {
                        $isFileOK = false;
                    }
                }
                ?>
            </tr>
        <?php endwhile ?>

    </table>

<?php
fclose($handle);
$content = ob_get_clean();

if ($isFileOK) {
    if ($warningLines) {
        echo Html::tag('div', 'Обратите внимание:', ['class' => 'alert alert-warning']);
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
        ];

        if ($warningLines) {
            $params['onClick'] = 'return confirm("Вы уверены?")';
        }

        echo $this->render('//layouts/_link', [
            'url' => Url::to(['/nnp/import/step4', 'countryCode' => $country->code, 'fileId' => $countryFile->id]),
            'text' => 'Импортировать файл',
            'glyphicon' => 'glyphicon-fast-forward',
            'params' => $params,
        ]);
//    }
} else {
    echo Html::tag('div', 'Импорт невозможен, потому что файл содержит ошибки. Исправьте ошибки в файле и загрузите его заново.', ['class' => 'alert alert-danger']);
    if ($errorLines) {
        echo "<ul>";
        foreach ($errorLines as $line) {
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
                    ])
                ),
                ['class' => 'alert alert-danger']
            );
        }
        echo "</ul>";
    }
}

echo $content;
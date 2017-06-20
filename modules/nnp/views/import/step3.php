<?php
/**
 * Предпросмотр файла (шаг 3/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFile $countryFile
 * @var int $offset
 * @var int $limit
 */

use app\modules\nnp\models\CountryFile;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$country = $countryFile->country;
?>

<?= app\classes\Html::formLabel($this->title = 'Предпросмотр файла (шаг 3/3)') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Импорт', 'url' => '/nnp/import/'],
        ['label' => $country->name_rus, 'url' => Url::to(['/nnp/import/step2/', 'countryCode' => $country->code])],
        ['label' => $this->title],
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
    echo 'Ошибка чтения файла';
    return;
}

$ndcTypes = NdcType::getList();
$rowNumber = 0;
$isFileOK = true;
?>

<?php ob_start() ?>

    <table class="table">

        <?= $this->render('_step3_th') ?>

        <?php while (($row = fgetcsv($handle)) !== false) : ?>

            <?php
            if ($rowNumber++ < $offset) :
                // пропустить эти строки
                if ($rowNumber == 1) :
                    // только 1 раз вывести ссылку "назад"
                    $newOffset = max(0, $offset - $limit);
                    ?>
                    <tr>
                        <td colspan="10">
                            <?= $this->render('//layouts/_buttonLink', [
                                'url' => Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id, 'offset' => $newOffset]),
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
                ?>
                <tr>
                    <td colspan="10">
                        <?= $this->render('//layouts/_buttonLink', [
                            'url' => Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $countryFile->id, 'offset' => $newOffset]),
                            'text' => sprintf('Проверить следующие строки (%d - %d)', $newOffset + 1, $newOffset + $limit),
                            'glyphicon' => 'glyphicon-menu-down',
                            'class' => 'btn-default btn-xs',
                        ]) ?>
                    </td>
                </tr>
                <?php
                break;
            endif;

            if ($rowNumber == 1 && !is_numeric($row[0])) {
                // Шапка (первая строчка с названиями полей) - пропустить
                continue;
            }
            ?>
            <tr>
                <?php for ($columnNumber = 0; $columnNumber <= 10; $columnNumber++) : ?>
                    <?php
                    $cell = isset($row[$columnNumber]) ? $row[$columnNumber] : '';
                    switch ($columnNumber) {

                        case 0:
                            // Код (префикс) страны. Непустое. Целое число. Например, 7
                            $isCellOK = is_numeric($cell) && $cell && in_array($cell, $country->getPrefixes());
                            break;

                        case 1:
                            // NDC. Для ABC/DEF непустое, для других типов NDC - можно пустое. Целое число. Например, 495
                            $cellNdcId = isset($row[3]) ? (int)$row[3] : 0;
                            if (in_array($cellNdcId, [NdcType::ID_GEOGRAPHIC, NdcType::ID_MOBILE])) {
                                $isCellOK = is_numeric($cell) && $cell;
                            } else {
                                $isCellOK = !$cell || is_numeric($cell);
                            }
                            break;

                        case 2:
                            // Исходный тип NDC. Сохраняется, но не используется. Например, Mobile
                            $isCellOK = !$cell || !is_numeric($cell); // число - это очень странно
                            break;

                        case 3:
                            // ID типа NDC. Непустое. Целое число. 1 - geo, 2 - mobile, 3 - nomadic, 4 - freephone, 5 - premium, 6 - short code и пр.
                            $isCellOK = is_numeric($cell) && $cell && isset($ndcTypes[$cell]);
                            break;

                        case 4:
                            // Диапазон с. Непустое. Строка (не число, чтобы не потерять ведущие нули!). Не должно быть букв, пробелов или другого форматирования разрядов. Например, 0000000
                            $isCellOK = is_numeric($cell) && $cell;
                            break;

                        case 5:
                            // Диапазон по. Непустое. Строка (не число, чтобы не потерять ведущие нули!). Не должно быть букв, пробелов или другого форматирования разрядов. Кол-во цифр должно быть таким же, как у предыдущего поля. Например, 0009999
                            $isCellOK = is_numeric($cell) && $cell && isset($row[4]) && strlen($row[4]) == strlen($cell);
                            break;

                        case 6:
                            // Исходный регион. Можно пустое. Например, Алтайский край
                            $isCellOK = !$cell || !is_numeric($cell); // число - это очень странно
                            break;

                        case 7:
                            // Исходный оператор. Можно пустое. Например, ПАО Мегафон
                            $isCellOK = !$cell || !is_numeric($cell); // число - это очень странно
                            break;

                        case 8:
                            // Дата принятия решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Если указано, то должна быть дата в любом из форматов: ГГГГ.ММ.ДД (Excel-формат), ГГГГ-ММ-ДД (SQL-формат), ММ/ДД/ГГГГ (американский формат), ДД-ММ-ГГГГ (европейский формат). Например, 2016.12.31
                            $cell = str_replace('.', '-', $cell); // ГГГГ.ММ.ДД преобразовать в ГГГГ-ММ-ДД. Остальные форматы strtotime распознает сам
                            $isCellOK = !$cell || strtotime($cell);
                            break;

                        case 9:
                            // Комментарий или номер решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Например, Приказ №12345/6
                            $isCellOK = !$cell || !is_numeric($cell); // число - это очень странно;
                            break;

                        case 10:
                            // Статус номера. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Например, Зарезервировано для спецслужб
                            $isCellOK = !$cell || !is_numeric($cell); // число - это очень странно;
                            break;

                        default:
                            throw new LogicException();
                    }

                    $isFileOK = $isFileOK && $isCellOK; // если есть хоть одна ошибка в ячейке - весь файл ошибочен
                    ?>
                    <td class="<?= $isCellOK ? 'success' : 'danger' ?>">
                        <?= $cell ?>
                    </td>
                <?php endfor ?>
            </tr>
        <?php endwhile ?>

    </table>

<?php
fclose($handle);
$content = ob_get_clean();

if ($isFileOK) {
    if (!NumberRange::isTriggerEnabled()) {
        echo $this->render('//layouts/_buttonLink', [
            'url' => Url::to(['/nnp/import/step4', 'countryCode' => $country->code, 'fileId' => $countryFile->id]),
            'text' => 'Импортировать файл',
            'glyphicon' => 'glyphicon-fast-forward',
            'class' => 'btn-success',
        ]);
    }
} else {
    echo Html::tag('div', 'Импорт невозможен, потому что файл содержит ошибки. Исправьте ошибки в файле и загрузите его заново.', ['class' => 'alert alert-danger']);
}

echo $content;
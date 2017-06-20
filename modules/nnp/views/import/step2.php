<?php
/**
 * Загрузить или выбрать файл (шаг 2/3)
 *
 * @var app\classes\BaseView $this
 * @var Country $country
 */

use app\modules\nnp\models\Country;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = $country->name_rus . '. Загрузить или выбрать файл (шаг 2/3)') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Импорт', 'url' => '/nnp/import/'],
        ['label' => $this->title],
    ],
]) ?>

<?= $this->render('//layouts/_buttonLink', [
    'url' => Url::to(['/nnp/import/']),
    'text' => 'Другая страна',
    'glyphicon' => 'glyphicon-step-backward',
    'class' => 'btn-default',
]) ?>

<div class="collapse" id="step2-upload-help">
    <p>
        Формат: текстовый.<br>
        Расширение: .csv (comma-separated value).<br>
        Архивирование: можно (а для больших файлов нужно) заархивировать zip. В этом случае расширение .csv.zip<br>
        Кодировка: юникод (UTF-8) без BOM.<br>
        Переводы строк: любые (как \n, так и \r\n).<br>
        Разделитель полей: запятая.<br>
        Разделитель текста: не обязателен. Если используется, то двойные кавычки.<br>
        Шапка (первая строчка с названиями полей): можно делать, можно не делать.<br>
        Шрифт, размер, цвет, выравнивание, жирность и пр.: не важно (в csv оно все равно не сохраняется).<br>
        Порядок столбцов:
    </p>
    <table class="table">
        <?= $this->render('_step3_th') ?>
        <tr>
            <td>7</td>
            <td>495</td>
            <td>Geo</td>
            <td>1</td>
            <td>0000000</td>
            <td>0009999</td>
            <td>Москва</td>
            <td>МГТС</td>
            <td>2016.12.31</td>
            <td>Приказ №12345/6</td>
            <td></td>
        </tr>
        <tr>
            <td>7</td>
            <td>901</td>
            <td>Mobile</td>
            <td>2</td>
            <td>0010000</td>
            <td>0019999</td>
            <td>Москва</td>
            <td>ПАО Мегафон</td>
            <td>12/31/2016</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>7</td>
            <td></td>
            <td>Специальный</td>
            <td>6</td>
            <td>112</td>
            <td>112</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Спецслужбы</td>
        </tr>
    </table>
</div>

<div class="row">
    <div class="col-sm-4">
        <?= $this->render('_step2_upload') ?>
    </div>
    <div class="col-sm-8">
        <?= $this->render('_step2_select_file', ['country' => $country]) ?>
    </div>
</div>

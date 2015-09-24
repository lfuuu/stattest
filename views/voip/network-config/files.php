<?php
use app\models\billing\Pricelist;
use app\models\billing\PricelistFile;
use app\models\billing\NetworkConfig;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\grid\GridView;

/** @var NetworkConfig $networkConfig */
/** @var Pricelist $pricelist */
/** @var PricelistFile[] $files */

echo Html::formLabel('Файлы');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Местные префиксы', 'url' => Url::toRoute(['voip/network-config/list'])],
        ['label' => $networkConfig->name, 'url' => Url::toRoute(['voip/network-config/edit', 'id' => $networkConfig->id])],
        'Файлы'
    ],
]);
?>

<table align="right" width="100%">
    <tr>
        <td nowrap>
            <form action="/voip/network-config/file-upload?networkConfigId=<?=$networkConfig->id?>" method="post" enctype='multipart/form-data'>
                <input type="file" name="upfile" />
                <input type="submit" class="btn-primary" value="Загрузить файл" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
        <td nowrap>
            <form action="/voip/network-config/geo-upload?networkConfigId=<?=$networkConfig->id?>" method="post">
                <input type="submit" class="btn-primary" value="Загрузить из Россвязи" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
    </tr>
</table>

&nbsp;

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'Активность',
            'format' => 'raw',
            'value' => function ($data) {
                if ($data->active) {
                    return Html::tag('span', '&nbsp;', [
                        'class' => 'btn btn-grid',
                        'style' => 'background:#C4DF9B; cursor: default;',
                        'title' => 'Активен',
                    ]);
                }
            },
            'width' => '50px',
            'hAlign' => 'center',
        ],
        [
            'label' => 'Начало действия',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->startdate, Url::toRoute(['/index.php', 'module' => 'voipnew', 'action' => 'network_file_show', 'id' => $data->id]));
            },
        ],
        [
            'label' => 'Имя файла',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->file_name, Url::toRoute(['/index.php', 'module' => 'voipnew', 'action' => 'network_file_show', 'id' => $data->id]));
            },
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => function ($data) {
                if ($data->store_filename) {
                    return Html::a('скачать', Url::toRoute(['/voip/network-config/file-download', 'fileId' => $data->id]));
                }
                return '';
            },
            'width' => '100px',
            'hAlign' => 'center',
        ],
        [
            'label' => 'Дата загрузки',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->created_at;
            },
        ],
        [
            'label' => 'Количество строк',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->rows;
            },
        ],
    ],
    'toolbar'=> [],
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
?>
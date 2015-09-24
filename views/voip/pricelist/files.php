<?php
use app\models\billing\Pricelist;
use app\models\billing\PricelistFile;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;
use app\classes\Html;

/** @var Pricelist $pricelist */
/** @var PricelistFile[] $files */

echo Html::formLabel('Файлы');
echo Breadcrumbs::widget([
    'links' => [
        [
            'label' => 'Прайс-листы',
            'url' => Url::toRoute([
                'voip/pricelist/list',
                'type' => $model->type,
                'orig' => $model->orig,
                'connectionPointId' => $model->connection_point_id,
            ])
        ],
        ['label' => $pricelist->name, 'url' => Url::toRoute(['voip/pricelist/edit', 'id' => $pricelist->id])],
        'Файлы'
    ],
]);
?>

<table align="right">
    <tr>
        <td nowrap>
            <form action="/voip/pricelist/file-upload?pricelistId=<?=$pricelist->id?>" method="post" enctype='multipart/form-data'>
                <input type="file" name="upfile" />
                <input type="submit" class="btn-primary" value="Загрузить файл" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>

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
                return Html::a($data->filename, Url::toRoute(['/index.php', 'module' => 'voipnew', 'action' => 'network_file_show', 'id' => $data->id]));
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
            'label' => 'Тип',
            'format' => 'raw',
            'value' => function ($data) {
                return ($data->full ? 'Полный' : 'Изменения');
            },
        ],
        [
            'label' => 'Дата загрузки',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->date;
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
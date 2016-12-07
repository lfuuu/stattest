<?php
/**
 * Main page view for number filling report (/voip/filling)
 */

use app\classes\grid\GridView;
use yii\widgets\Breadcrumbs;
use kartik\daterange\DateRangePicker;
use yii\widgets\Pjax;

?>

<?= app\classes\Html::formLabel($this->title = 'Загрузка номера') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);
?>

<div class="well" style="overflow-x: auto; margin-bottom: -20px;">
            <form method="GET" action="/voip/filling" data-pjax>
                <div class="col-sm-8">
                    <legend style="font-size: 16px;">Фильтр</legend>
                    <table border="0" width="40%">
                        <thead>
                        <tr>
                            <th>Период</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <?php
                                    $dateStart = $dataProvider->params[':date_start'] ? $dataProvider->params[':date_start'] : date('Y-m-d', strtotime('yesterday'));
                                    $dateEnd = $dataProvider->params[':date_end'] ? $dataProvider->params[':date_end'] : date('Y-m-d');
                                    echo DateRangePicker::widget([
                                        'name' => 'date',
                                        'presetDropdown' => true,
                                        'hideInput' => true,
                                        'value' => "$dateStart : $dateEnd",
                                        'pluginOptions' => [
                                            'locale' => [
                                                'format' => 'YYYY-MM-DD',
                                                'separator'=>' : ',
                                            ],
                                        ],
                                    ]);
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-sm-4">
                    <legend style="font-size: 16px;">Введите номер</legend>
                    <table border="0" width="100%" style="margin-top: 32px;">
                        <tbody>
                        <tr>
                            <td>
                                <input id="number" type="text" class="form-control" name="number" value="<?= $dataProvider->params[':number'] ?>"
                                       placeholder="Проверяемый номер"></td>
                            <td>
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary">Искать</button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

<?php
Pjax::begin(['formSelector' => '.well form[data-pjax]',
             'linkSelector' => false,
             'enableReplaceState' => true,
             'timeout' => 180000]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label'     => 'Интервал',
            'attribute' => 'interval'
        ],
        [
            'label'     => 'Количество линий',
            'attribute' => 'lines_count'
        ],
        [
            'label'     => 'Количество минут',
            'attribute' => 'minutes_count'
        ],
        [
            'label'     => 'Загрузка',
            'attribute' => 'filling'
        ]
    ],
    'pjax' => true,
    'panelHeadingTemplate' => ''
]);
Pjax::end();

?>
<img id="preloader" src="/images/preloader.png" style="position: fixed; z-index: 9999; left: calc(50% - 32px); top: calc(50% - 32px); display: none">
<script>
    $(document)
        .on('pjax:send', function ()
        {
            $('#preloader').show();
        })
        .on('pjax:complete', function ()
        {
            $('#preloader').hide();
        });
</script>

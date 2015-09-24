<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;

$modes = [];
foreach ($operator->requestModes as $mode => $params) {
    $modes[$mode] = $params['title'];
}
?>

<div class="well" style="padding-top: 60px;">
    <legend>Заказы</legend>


    <form method="GET">
        <div class="col-xs-12">
            <table border="0" width="100%">
                <colgroup>
                    <col width="30%" />
                    <col width="20%" />
                    <col width="20%" />
                    <col width="*" />
                </colgroup>
                <thead>
                <tr>
                    <th style="font-size: 12px;">Период</th>
                    <th style="font-size: 12px;"><span style="padding-left: 14px">Состояние</span></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php
                        echo DateRangePicker::widget([
                            'name' => 'filter[range]',
                            'presetDropdown' => true,
                            'hideInput' => true,
                            'value' => $filter['dateFrom'] . ' : ' . $filter['dateTo'],
                            'pluginOptions' => [
                                'format' => 'YYYY-MM-DD',
                                'separator'=>' : ',
                            ],
                        ]);
                        ?>
                    </td>
                    <td>
                        <div class="col-xs-12">
                            <?php
                            echo Select2::widget([
                                'name' => 'filter[mode]',
                                'data' => $modes,
                                'value' => $filter['mode'],
                                'options' => [
                                    'placeholder' => '-- Выбрать --'
                                ]
                            ]);
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        echo Html::submitButton('Применить', ['class' => 'btn btn-primary',]);
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </form>

    <legend style="padding-top: 20px; font-size: 16px;">
        Отчет

        <?php if ($filter['mode']): ?>
            <?php if ($filter['dateFrom'] && $filter['dateTo']): ?>
                за период с <?= $filter['dateFrom']; ?> по <?= $filter['dateTo']; ?>
            <?php endif; ?>

            <?php if (isset($operator->requestModes[ $filter['mode'] ])): ?>
                в состоянии "<?= $operator->requestModes[ $filter['mode'] ]['title']; ?>"
            <?php endif; ?>
        <?php endif; ?>
    </legend>

    <?php if ($filter['mode']): ?>

        <?php if ($filter['mode'] == 'close'): ?>
            <form method="GET">
                <input type="hidden" name="filter[range]" value="<?= $filter['dateFrom'] . ' : ' . $filter['dateTo']; ?>" />
                <input type="hidden" name="filter[mode]" value="<?= $filter['mode']; ?>" />
                <input type="hidden" name="as-file" value="1" />
                <div style="float: right;">
                    <?=
                    Html::submitButton('Экспорт в Excel', [
                        'class' => 'btn btn-link',
                    ]);
                    ?>
                </div>
            </form>
            <div style="clear: both; height: 15px;"></div>
        <?php endif; ?>

        <?php
        echo $this->render('@app/views/reports/' . $operator->operator . '/table.php', [
            'operator' => $operator,
            'filter' => $filter,
            'report' => $report,
            'billLink' => Url::toRoute(['/site/set-state', 'bill_no' => '']),
        ]);
        ?>
    <?php endif; ?>

</div>

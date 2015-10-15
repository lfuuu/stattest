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

<div class="well">
    <legend>Заказы</legend>


    <form method="GET">
        <div class="col-xs-12">
            <table border="0" width="100%">
                <colgroup>
                    <col width="30%" />
                    <col width="*" />
                </colgroup>
                <thead>
                <tr>
                    <th style="font-size: 12px;">Период</th>
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
                    <td align="right">
                        <div class="col-xs-12">
                            <?php foreach ($operator->requestModes as $mode => $params) :?>
                                <?php
                                echo Html::submitButton($params['title'], [
                                    'name' => 'filter[mode]',
                                    'value' => $mode,
                                    'class' => 'btn ' . ($mode == $filter['mode'] ? 'btn-primary' : 'btn-default'),
                                    'style' => 'margin-right: 5px; width: 110px;',
                                ]);
                                ?>
                            <?php endforeach;?>
                        </div>
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
